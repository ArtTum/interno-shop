<?php

namespace App\Imports\ERP;

use App\Constants\AttributeConstants;
use App\Constants\UploadConstant;
use App\Events\ReloadPagePublic;
use App\Models\AttributeType;
use App\Models\AttributeTypeTranslation;
use App\Models\Language;
use App\Models\UploadLog;
use App\Repositories\AttributeType\AttributeTypeRepository;
use App\Repositories\AttributeTypeTranslation\AttributeTypeTranslationRepository;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\UploadLog\UploadLogRepository;
use App\Services\General\CustomSlugService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportAttributeTypes implements ToCollection
{
    private $upload;
    private string $importType;
    private string $vendorKey;
    private UploadLogRepository $uploadLogRepository;
    private AttributeTypeRepository $attributeTypeRepository;
    private AttributeTypeTranslationRepository $attributeTypeTranslationRepository;
    private LanguageRepository $languageRepository;
    public function __construct($upload, string $importType, string $vendorKey)
    {
        $this->upload = $upload;
        $this->importType = $importType;
        $this->vendorKey = $vendorKey;
        $this->uploadLogRepository = new UploadLogRepository(new UploadLog());
        $this->attributeTypeRepository = new AttributeTypeRepository(new AttributeType());
        $this->attributeTypeTranslationRepository = new AttributeTypeTranslationRepository(new AttributeTypeTranslation());
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
                    if (count($data) !== count(UploadConstant::ATTRIBUTE_TYPES_FILE_HEADERS)) {
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

                $attributeType = null;
                $attributeTypeTranslation = null;

                DB::beginTransaction();
                try {
                    try {
                        $languageId = $this->languageRepository->getIdByCode($data[4]);
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
                            'log' => "<span class='text-danger'>Error at line {$row}</span>: Not correct locale: {$data[4]}",
                        ];
                        $invalidLines++;
                        continue;
                    }

                    $attributeTypePreparedArray = [
                        'default_sort_order' => AttributeConstants::DEFAULT_SORT_ORDERS[$data[1]],
                        'type' => AttributeConstants::TYPES[$data[2]],
                        'logic' => AttributeConstants::ATTRIBUTE_LOGIC[$data[3]]
                    ];

                    if (!empty($data[0])) {
                        $attributeType = $this->attributeTypeRepository->fetchByFieldWithLanguage('id', $data[0], 'id', $languageId);
                    }

                    if (!$attributeType) {
                        $attributeType = $this->attributeTypeRepository->create($attributeTypePreparedArray);
                    } else {
                        $this->attributeTypeRepository->update('id', $attributeType->id, $attributeTypePreparedArray);
                    }

                    $attributeTypeTranslation = $this->attributeTypeTranslationRepository->fetchByAttributeTypeAndLanguageId($attributeType->id, $languageId, 'id, slug');

                    $attributeTypeTranslationPreparedArray = [
                        'attribute_type_id' => $attributeType->id,
                        'language_id' => $languageId,
                        'name' => $data[5],
                        'label' => $data[7],
                    ];

                    if ($attributeTypeTranslation) {
                        if ($attributeTypeTranslation->slug !== $data[6] || empty($data[6])) {
                            $slugValue = !empty($data[6]) ? $data[6] : $data[5];
                            $slug = CustomSlugService::createCustomSlug(
                                new AttributeTypeTranslation(),
                                $slugValue,
                                $languageId
                            );
                        } else {
                            $slug = $data[6];
                        }
                    } else {
                        $slugValue = !empty($data[6]) ? $data[6] : $data[5];
                        $slug = CustomSlugService::createCustomSlug(
                            new AttributeTypeTranslation(),
                            $slugValue,
                            $languageId
                        );
                    }

                    $attributeTypeTranslationPreparedArray['slug'] = $slug;

                    if ($this->importType == 1) {
                        $this->attributeTypeTranslationRepository->insert($attributeTypeTranslationPreparedArray);
                    } else if ($this->importType == 2 || $this->importType == 3) {
                        if (!$attributeTypeTranslation) {
                            $this->attributeTypeTranslationRepository->insert($attributeTypeTranslationPreparedArray);
                        } else {
                            unset($attributeTypeTranslationPreparedArray['slug']);
                            $this->attributeTypeTranslationRepository->update('id', $attributeTypeTranslation->id, $attributeTypeTranslationPreparedArray);
                        }
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
            broadcast(new ReloadPagePublic('update-attribute-types-page'));

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
        if ($this->importType == 2 && empty($data[0])) {
            return [
                'success' => false,
                'errors' => ["ID is important for update"],
            ];
        } else if ($this->importType == 1 && !empty($data[0])) {
            return [
                'success' => false,
                'errors' => ["Inserting attribute can't have ID"],
            ];
        }

        if (!array_key_exists($data[1], AttributeConstants::DEFAULT_SORT_ORDERS)) {
            return [
                'success' => false,
                'errors' => ["Invalid attribute default sort order value"],
            ];
        }

        if (!array_key_exists($data[2], AttributeConstants::TYPES)) {
            return [
                'success' => false,
                'errors' => ["Invalid attribute type value"],
            ];
        }

        if (!array_key_exists($data[3], AttributeConstants::ATTRIBUTE_LOGIC)) {
            return [
                'success' => false,
                'errors' => ["Invalid attribute logic value"],
            ];
        }

        $preparedValidationData = [
            'id' => $data[0],
            'default_sort_order' => AttributeConstants::DEFAULT_SORT_ORDERS[$data[1]],
            'type' => AttributeConstants::TYPES[$data[2]],
            'logic' => AttributeConstants::ATTRIBUTE_LOGIC[$data[3]],
            'language_code' => $data[4],
            'name' => $data[5],
            'slug' => $data[6],
            'label' => $data[7],
        ];

        $validator = Validator::make($preparedValidationData, [
            'id' => 'nullable|integer|exists:attribute_types,id',
            'default_sort_order' => 'required|integer',
            'type' => 'required|integer',
            'logic' => 'required|integer',
            'language_code' => 'required|exists:languages,code',
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:250',
            'label' => 'string|max:250',
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
