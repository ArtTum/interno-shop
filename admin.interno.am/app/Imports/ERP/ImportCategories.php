<?php

namespace App\Imports\ERP;

use App\Constants\UploadConstant;
use App\Events\ReloadPagePublic;
use App\Jobs\Category\RemoveCache;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Language;
use App\Models\ProductVariant;
use App\Models\UploadLog;
use App\Repositories\Category\CategoryRepository;
use App\Repositories\CategoryTranslation\CategoryTranslationRepository;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\ProductVariant\ProductVariantRepository;
use App\Repositories\UploadLog\UploadLogRepository;
use App\Services\General\CustomSlugService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportCategories implements ToCollection
{
    private $upload;
    private string $importType;
    private string $vendorKey;
    private UploadLogRepository $uploadLogRepository;
    private CategoryRepository $categoryRepository;
    private CategoryTranslationRepository $categoryTranslationRepository;
    private LanguageRepository $languageRepository;
    private ProductVariantRepository $productVariantRepository;

    public function __construct($upload, string $importType, string $vendorKey)
    {
        $this->upload = $upload;
        $this->importType = $importType;
        $this->vendorKey = $vendorKey;
        $this->uploadLogRepository = new UploadLogRepository(new UploadLog());
        $this->categoryRepository = new CategoryRepository(new Category());
        $this->categoryTranslationRepository = new CategoryTranslationRepository(new CategoryTranslation());
        $this->languageRepository = new LanguageRepository(new Language());
        $this->productVariantRepository = new ProductVariantRepository(new ProductVariant());
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
                    if (count($data) !== count(UploadConstant::CATEGORIES_FILE_HEADERS)) {
                        throw new \Exception('Columns count is wrong');
                    }
                    continue;
                }
                $totalLines++;
                $logsLoopNumber++;

                try {
                    $languageId = $this->languageRepository->getIdByCode($data[8]);
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
                        'log' => "<span class='text-danger'>Error at line {$row}</span>: Not correct locale: {$data[8]}",
                    ];
                    $invalidLines++;
                    continue;
                }

                if (!empty($data[1])) {
                    $parentTranslationId = $this->categoryTranslationRepository->getIdByCategoryIdAndLanguageId($data[1], $languageId);
                    if (!$parentTranslationId) $parentTranslationId = 999999999999999;
                } else {
                    $parentTranslationId = null;
                }

                if (!empty($data[9])) {
                    $relatedTranslationId = $this->categoryTranslationRepository->getIdByCategoryIdAndLanguageId($data[9], $languageId);
                    if (!$relatedTranslationId) $relatedTranslationId = 999999999999999;
                } else {
                    $relatedTranslationId = null;
                }

                $type = null;
                if ($data[2] === 'Link') {
                    $type = 0;
                } else if ($data[2] === 'Add to cart') {
                    $type = 1;
                } else if ($data[2] === 'With popup') {
                    $type = 2;
                } else if ($data[2] === 'Variables with variations') {
                    $type = 3;
                }

                $validator = $this->validate($data, $parentTranslationId, $relatedTranslationId, $type);

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

                $category = null;
                $categoryTranslation = null;

                DB::beginTransaction();
                try {
                    $categoryPreparedArray = [
                        'parent_id' => !empty($data[1]) ? $data[1] : null,
                        'products_showing_type' => $type,
                        'price_adjustment' => !empty($data[7]) ? str_replace(',', '.', $data[7]) : null,
                        'responsive_settings' => [
                            'desktop' => $data[4] ?? 4,
                            'tablet' => $data[5] ?? 2,
                            'mobile' => $data[6] ?? 1,
                        ],
                        'media_id' => !empty($data[3]) ? $data[3] : null,
                    ];

                    if (!empty($data[0])) {
                        $category = $this->categoryRepository->fetchByField('id', $data[0], 'id');
                    }

                    if (!$category) {
                        $category = $this->categoryRepository->create($categoryPreparedArray);
                    } else {
                        $this->categoryRepository->update('id', $category->id, $categoryPreparedArray);
                        $this->productVariantRepository->calculatePriceAdjustment($categoryPreparedArray['price_adjustment'], $category->id);
                    }

                    Artisan::call('category:remove-cache', [
                        'categoryId' => $category->id,
                        'languageId' => $languageId,
                        'vendorKey' => $this->vendorKey
                    ]);

                    $categoryTranslation = $this->categoryTranslationRepository->fetchByCategoryAndLanguageId($category->id, $languageId, 'id, slug');

                    $categoryTranslationPreparedArray = [
                        'category_id' => $category->id,
                        'language_id' => $languageId,
                        'parent_id' => $parentTranslationId,
                        'related_category_translation_id' => $relatedTranslationId,
                        'name' => !empty($data[10]) ? $data[10] : null,
                        'description' => !empty($data[12]) ? $data[12] : null,
                        'meta_title' => !empty($data[13]) ? $data[13] : null,
                        'meta_keywords' => !empty($data[14]) ? $data[14] : null,
                        'meta_description' => !empty($data[15]) ? $data[15] : null
                    ];

                    if ($categoryTranslation) {
                        if ($categoryTranslation->slug !== $data[11] || empty($data[11])) {
                            $slugValue = !empty($data[11]) ? $data[11] : $data[10];
                            $slug = CustomSlugService::createCustomSlug(
                                new CategoryTranslation(),
                                $slugValue,
                                $languageId
                            );
                        } else {
                            $slug = $data[11];
                        }
                    } else {
                        $slugValue = !empty($data[11]) ? $data[11] : $data[10];
                        $slug = CustomSlugService::createCustomSlug(
                            new CategoryTranslation(),
                            $slugValue,
                            $languageId
                        );
                    }
                    $categoryTranslationPreparedArray['slug'] = $slug;

                    if ($this->importType == 1) {
                        $this->categoryTranslationRepository->insert($categoryTranslationPreparedArray);
                    } else if ($this->importType == 2 || $this->importType == 3) {
                        if (!$categoryTranslation) {
                            $this->categoryTranslationRepository->insert($categoryTranslationPreparedArray);
                        } else {
                            unset($categoryTranslationPreparedArray['slug']);
                            $this->categoryTranslationRepository->update('id', $categoryTranslation->id, $categoryTranslationPreparedArray);
                        }
                    }

                    RemoveCache::dispatch($this->vendorKey, $category->id, $languageId);

                    DB::commit();
                } catch (\Exception $exception) {
                    Log::error("Job failed during execution: {$exception}");
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
            broadcast(new ReloadPagePublic('update-categories-page'));
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

    private function validate(array $data, ?int $parentTranslationId, ?int $relatedTranslationId, ?int $type): array
    {
        if ($this->importType == 2 && empty($data[0])) {
            return [
                'success' => false,
                'errors' => ["ID is important for update"],
            ];
        } else if ($this->importType == 1 && !empty($data[0])) {
            return [
                'success' => false,
                'errors' => ["Inserting category can't have ID"],
            ];
        }

        $preparedValidationData = [
            'id' => $data[0],
            'parent_id' => !empty($data[1]) ? $data[1] : null,
            'parent_translation_id' => $parentTranslationId,
            'products_showing_type' => $type,
            'related_category_translation_id' => $relatedTranslationId,
            'media_id' => !empty($data[3]) ? $data[3] : null,
            'desktop' => $data[4],
            'tablet' => $data[5],
            'mobile' => $data[6],
            'price_adjustment' => !empty($data[7]) ? str_replace(',', '.', $data[7]) : null,
            'language_code' => $data[8],
            'name' => $data[10],
            'slug' => $data[11],
            'description' => $data[12],
            'meta_title' => $data[13],
            'meta_keywords' => $data[14],
            'meta_description' => $data[15],
        ];

        $validator = Validator::make($preparedValidationData, [
            'id' => 'nullable|integer|exists:categories,id',
            'parent_id' => 'nullable|exists:categories,id',
            'parent_translation_id' => 'nullable|exists:category_translations,id',
            'media_id' => 'nullable|integer|exists:media,id',
            'products_showing_type' => 'required|integer',
            'related_category_translation_id' => 'nullable|exists:category_translations,id',
            'desktop' => 'integer',
            'tablet' => 'integer',
            'mobile' => 'integer',
            'price_adjustment' => 'nullable|numeric',
            'language_code' => 'required|exists:languages,code',
            'name' => 'required|string|max:50',
            'slug' => 'nullable|string|max:250',
            'description' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'meta_description' => 'nullable|string',
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
