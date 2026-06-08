<?php

namespace App\Imports\ERP;

use App\Constants\UploadConstant;
use App\Events\ReloadPagePublic;
use App\Models\Language;
use App\Models\UploadLog;
use App\Models\Variable;
use App\Models\VariableTranslation;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\UploadLog\UploadLogRepository;
use App\Repositories\Variable\VariableRepository;
use App\Repositories\VariableTranslation\VariableTranslationRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportTranslations implements ToCollection
{
    private $upload;
    private string $vendorKey;
    private UploadLogRepository $uploadLogRepository;
    private LanguageRepository $languageRepository;
    private VariableRepository $variableRepository;
    private VariableTranslationRepository $variableTranslationRepository;

    public function __construct($upload, string $vendorKey)
    {
        $this->upload = $upload;
        $this->vendorKey = $vendorKey;
        $this->variableRepository = new VariableRepository(new Variable());
        $this->variableTranslationRepository = new VariableTranslationRepository(new VariableTranslation());
        $this->uploadLogRepository = new UploadLogRepository(new UploadLog());
        $this->languageRepository = new LanguageRepository(new Language());
    }

    public function collection(Collection $collection): void
    {
        setDBConnection($this->vendorKey);
        $invalidLines = 0;
        $totalLines = 0;
        $logsLoopNumber = 0;
        try {
            foreach ($collection as $row => $data) {
                $data = $data->toArray();
                if ($row == 0) {
                    if (count($data) !== count(UploadConstant::TRANSLATIONS_FILE_HEADERS)) {
                        throw new \Exception('Columns count is wrong');
                    }
                    continue;
                }
                $totalLines++;
                $logsLoopNumber++;

                $validator = $this->validate($data);

                if (!$validator['success']) {
                    foreach ($validator['errors'] as $error) {
                        $logs[] = [
                            'upload_id' => $this->upload->id,
                            'log' => "<span class='text-danger'>Error at line {$row}</span>: {$error}",
                        ];
                    }
                    $invalidLines++;
                    continue;
                }

                $variableTranslation = null;

                DB::beginTransaction();
                try {
                    try {
                        $languageId = $this->languageRepository->getIdByCode($data[2]);
                    } catch (\Exception $exception) {
                        $logs[] = [
                            'upload_id' => $this->upload->id,
                            'log' => "<span class='text-danger'>Error at line {$row}</span>: {$exception->getMessage()}",
                        ];
                        $invalidLines++;
                        continue;
                    }

                    if (!$languageId) {
                        $logs[] = [
                            'upload_id' => $this->upload->id,
                            'log' => "<span class='text-danger'>Error at line {$row}</span>: Not correct locale: {$data[2]}",
                        ];
                        $invalidLines++;
                        continue;
                    }

                    $variable = $this->variableRepository->fetchByField('key', $data[0], 'id');

                    $variableTranslation = $this->variableTranslationRepository->fetchByVariableAndLanguageId($variable->id, $languageId, 'id');

                    $variableTranslationPreparedArray = [
                        'variable_id' => $variable->id,
                        'language_id' => $languageId,
                        'value' => $data[3],
                    ];

                    if (!$variableTranslation) {
                        $this->variableTranslationRepository->insert($variableTranslationPreparedArray);
                    } else {
                        $this->variableTranslationRepository->update('id', $variableTranslation->id, $variableTranslationPreparedArray);
                    }
                    DB::commit();
                } catch (\Exception $exception) {
                    $logs[] = [
                        'upload_id' => $this->upload->id,
                        'log' => "<span class='text-danger'>Error at line {$row}</span>: {$exception->getMessage()}",
                    ];
                    $invalidLines++;
                    DB::rollBack();
                    continue;
                }

                if ($logsLoopNumber === 50) {
                    if (!empty($logs)) $this->uploadLogRepository->insert($logs);
                    $logsLoopNumber = 0;
                }
            }

            if (!empty($logs)) $this->uploadLogRepository->insert($logs);

            $this->upload->update(
                [
                    'status' => UploadConstant::STATUSES['Completed'],
                    'total_lines' => $totalLines,
                    'invalid_lines' => $invalidLines,
                    'succeed_lines' => $totalLines - $invalidLines,
                ]
            );

            broadcast(new ReloadPagePublic('update-uploads-page'));
            broadcast(new ReloadPagePublic('update-translations-page'));
        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }
    }

    public function chunkSize(): int
    {
        return 1000; // Number of rows per chunk
    }

    public function batchSize(): int
    {
        return 1000; // Number of rows per batch
    }

    private function validate(array $data): array
    {
        if (empty($data[0])) {
            return [
                'success' => false,
                'errors' => ["Key is important for update"],
            ];
        }

        $preparedValidationData = [
            'key' => $data[0],
            'language_code' => $data[2],
            'value' => !empty($data[3]) ? $data[3] : null,
        ];

        $validator = Validator::make($preparedValidationData, [
            'key' => 'required|exists:variables,key',
            'language_code' => 'required|exists:languages,code',
            'value' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->all(),
            ];
        } else {
            return [
                'success' => true
            ];
        }
    }
}
