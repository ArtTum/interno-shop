<?php

namespace App\Imports\ERP;

use App\Constants\UploadConstant;
use App\Events\ReloadPagePublic;
use App\Models\Language;
use App\Models\MediaTranslation;
use App\Models\UploadLog;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\MediaTranslation\MediaTranslationRepository;
use App\Repositories\UploadLog\UploadLogRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportMedia implements ToCollection
{
    private $upload;
    private string $vendorKey;
    private UploadLogRepository $uploadLogRepository;
    private LanguageRepository $languageRepository;
    private MediaTranslationRepository $mediaTranslationRepository;
    public function __construct($upload, string $vendorKey)
    {
        $this->upload = $upload;
        $this->vendorKey = $vendorKey;
        $this->mediaTranslationRepository = new MediaTranslationRepository(new MediaTranslation());
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
                    if (count($data) !== count(UploadConstant::MEDIA_FILE_HEADERS)) {
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

                $translation = null;

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

                    $translation = $this->mediaTranslationRepository->fetchByFieldLanguage('id', $data[0], 'id', ['language_id' => $languageId]);

                    $mediaTranslationPreparedArray = [
                        'media_id' => $data[0],
                        'language_id' => $languageId,
                        'alt' => $data[3],
                    ];

                    if (!$translation) {
                        $this->mediaTranslationRepository->insert($mediaTranslationPreparedArray);
                    } else {
                        $this->mediaTranslationRepository->update('id', $translation->id, $mediaTranslationPreparedArray);
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
        } catch (\Exception $exception) {
            throw new \Exception($exception);
        }
    }

    private function validate(array $data): array
    {
        if (empty($data[0])) {
            return [
                'success' => false,
                'errors' => ["ID is important for update"],
            ];
        }

        $preparedValidationData = [
            'id' => $data[0],
            'language_code' => $data[2],
            'alt' => !empty($data[3]) ? $data[3] : null,
        ];

        $validator = Validator::make($preparedValidationData, [
            'id' => 'required|exists:media,id',
            'language_code' => 'required|exists:languages,code',
            'alt' => 'required|string|max:255',
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

    public function chunkSize(): int
    {
        return 1000; // Number of rows per chunk
    }

    public function batchSize(): int
    {
        return 1000; // Number of rows per batch
    }
}
