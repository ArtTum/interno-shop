<?php

namespace App\Imports\ERP;

use App\Constants\MediaConstants;
use App\Constants\UploadConstant;
use App\Events\ReloadPagePublic;
use App\Jobs\Product\RemoveCache;
use App\Models\Attribute;
use App\Models\Item;
use App\Models\Language;
use App\Models\Media;
use App\Models\MediaSetting;
use App\Models\ProductVariant;
use App\Models\ProductVariantAttribute;
use App\Models\ProductVariantCustomFieldTranslation;
use App\Models\ProductVariantGallery;
use App\Models\ProductVariantParent;
use App\Models\ProductVariantTranslation;
use App\Models\ProductVariantTranslationGallery;
use App\Models\UploadLog;
use App\Repositories\Attribute\AttributeRepository;
use App\Repositories\Item\ItemRepository;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\Media\MediaRepository;
use App\Repositories\MediaSetting\MediaSettingRepository;
use App\Repositories\ProductVariant\ProductVariantRepository;
use App\Repositories\ProductVariantAttribute\ProductVariantAttributeRepository;
use App\Repositories\ProductVariantCustomFieldTranslation\ProductVariantCustomFieldTranslationRepository;
use App\Repositories\ProductVariantGallery\ProductVariantGalleryRepository;
use App\Repositories\ProductVariantParent\ProductVariantParentRepository;
use App\Repositories\ProductVariantTranslation\ProductVariantTranslationRepository;
use App\Repositories\ProductVariantTranslationGallery\ProductVariantTranslationGalleryRepository;
use App\Repositories\UploadLog\UploadLogRepository;
use App\Rules\ValidParentSkusExist;
use App\Rules\ValidParentSkusFormat;
use App\Services\ERP\File\FileService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportProductVariants implements ToCollection
{
    private $upload;
    private string $importType;
    private string $vendorKey;
    private array $customFieldsKeys = [];
    private int $lastIndexWithoutMetaFields = 0;
    private UploadLogRepository $uploadLogRepository;
    private ProductVariantRepository $productVariantRepository;
    private ProductVariantCustomFieldTranslationRepository $productVariantCustomFieldTranslationRepository;
    private ProductVariantGalleryRepository $productVariantGalleryRepository;
    private AttributeRepository $attributeRepository;
    private MediaRepository $mediaRepository;
    private ProductVariantParentRepository $productVariantParentRepository;
    private LanguageRepository $languageRepository;
    private FileService $fileService;
    private MediaSettingRepository $mediaSettingRepository;
    private ItemRepository $itemRepository;
    private ProductVariantTranslationRepository $productVariantTranslationRepository;
    private ProductVariantTranslationGalleryRepository $productVariantTranslationGalleryRepository;
    private ProductVariantAttributeRepository $productVariantAttributeRepository;

    private int $userId;

    private $iniProductVariantId;
    private $iniProductName;
    private $iniProductId;
    private $iniAttr1;
    private $iniAttr2;
    private $iniAttr3;
    private $iniAttr4;
    private $iniSku;
    private $iniMediaId;
    private $iniStockStatus;
    private $iniBasePrice;
    private $iniRegularPrice;
    private $iniSalesPrice;
    private $iniTaxStatus;
    private $iniStatus;
    private $iniParentSKUs;
    private $iniGalleryIds;
    private $iniLocale;
    private $iniName;
    private $iniShortDescription;
    private $iniDescription;
    private $iniDeleteFull;
    private $iniDeleteTranslation;
    private array $fileHeaders;
    private $iniBaseImgTranslated;
    private $iniGalleryIdsTranslated;
    private $iniSizeByUnit;
    private $iniUnit;

    public function __construct($upload, string $importType, string $vendorKey, int $userId)
    {
        Log::channel('product-upload-info')->info('__construct of ImportProductVariants');
        $this->upload = $upload;
        $this->importType = $importType;
        $this->vendorKey = $vendorKey;
        $this->uploadLogRepository = new UploadLogRepository(new UploadLog());
        $this->productVariantRepository = new ProductVariantRepository(new ProductVariant());
        $this->productVariantParentRepository = new ProductVariantParentRepository(new ProductVariantParent());
        $this->productVariantCustomFieldTranslationRepository = new ProductVariantCustomFieldTranslationRepository(new ProductVariantCustomFieldTranslation());
        $this->productVariantGalleryRepository = new ProductVariantGalleryRepository(new ProductVariantGallery());
        $this->productVariantTranslationGalleryRepository = new ProductVariantTranslationGalleryRepository(new ProductVariantTranslationGallery());
        $this->attributeRepository = new AttributeRepository(new Attribute());
        $this->mediaRepository = new MediaRepository(new Media());
        $this->languageRepository = new LanguageRepository(new Language());
        $this->fileService = new FileService();
        $this->mediaSettingRepository = new MediaSettingRepository(new MediaSetting());
        $this->itemRepository = new ItemRepository(new Item());
        $this->productVariantTranslationRepository = new ProductVariantTranslationRepository(new ProductVariantTranslation());
        $this->productVariantAttributeRepository = new ProductVariantAttributeRepository(new ProductVariantAttribute());
        $this->userId = $userId;
    }

    public function collection(Collection $collection): void
    {
        Log::channel('product-upload-info')->info('Start of ImportProductVariants');

        $invalidLines = 0;
        $logs = [];
        $cacheClearingProducts = [];
        $logsLoopNumber = 0;
        $totalLines = 0;

        setDBConnection($this->vendorKey);
        try {
            foreach ($collection as $row => $data) {
                $data = $data->toArray();

                Log::channel('product-upload-info')->info("Row started: {$row}");
                if ($row == 0) {
                    if (count($data) !== count(UploadConstant::PRODUCT_VARIATIONS_FILE_HEADERS)) {
                        throw new \Exception('Columns count is wrong');
                    }

                    $this->fileHeaders = $this->prepareHeadersFile($data);

                    foreach ($data as $index => $value) {
                        if (empty($value) || !str_starts_with($value, 'Meta:')) {
                            $this->lastIndexWithoutMetaFields = $index;
                            continue;
                        }

                        $customFieldKey = trim(substr(trim($value), 5));
                        $this->customFieldsKeys[$customFieldKey] = $index;
                    }

                    continue;
                }

                $totalLines++;

                $logsLoopNumber++;

                Log::channel('product-upload-info')->info("File headers:");
                Log::channel('product-upload-info')->info($this->fileHeaders);
                $row++;

                $this->iniProductVariantId = !empty($data[0]) ? $data[0] : null;
                $this->iniProductName = !empty($data[1]) ? $data[1] : null;
                $this->iniProductId = !empty($data[2]) ? $data[2] : null;
                $this->iniAttr1 = !empty($data[3]) ? $data[3] : null;
                $this->iniAttr2 = !empty($data[4]) ? $data[4] : null;
                $this->iniAttr3 = !empty($data[5]) ? $data[5] : null;
                $this->iniAttr4 = !empty($data[6]) ? $data[6] : null;
                $this->iniSku = !empty($data[7]) ? $data[7] : null;
                $this->iniParentSKUs = !empty($data[8]) ? $data[8] : null;
                $this->iniStockStatus = !empty($data[9]) ? $data[9] : null;
                $this->iniBasePrice = !empty($data[10]) ? str_replace(',', '.', $data[10]) : null;
                $this->iniRegularPrice = !empty($data[11]) ? str_replace(',', '.', $data[11]) : null;
                $this->iniSalesPrice = !empty($data[12]) ? str_replace(',', '.', $data[12]) : null;
                $this->iniTaxStatus = !empty($data[13]) ? $data[13] : null;
                $this->iniStatus = !empty($data[14]) ? $data[14] : null;
                $this->iniMediaId = !empty($data[15]) ? $data[15] : null;
                $this->iniGalleryIds = !empty($data[16]) ? explode(',', $data[16]) : [];
                $this->iniDeleteFull = !empty($data[17]) ? $data[17] : null;
                $this->iniLocale = !empty($data[18]) ? $data[18] : null;
                $this->iniName = !empty($data[19]) ? $data[19] : null;
                $this->iniShortDescription = !empty($data[20]) ? $data[20] : null;
                $this->iniDescription = !empty($data[21]) ? replace_h1_tags($data[21]) : null;
                $this->iniBaseImgTranslated = !empty($data[22]) ? $data[22] : null;
                $this->iniGalleryIdsTranslated = !empty($data[23]) ? explode(',', $data[23]) : [];
                $this->iniSizeByUnit = !empty($data[24]) ? str_replace(',', '.', $data[24]) : null;
                $this->iniUnit = !empty($data[25]) ? $data[25] : null;
                $this->iniDeleteTranslation = !empty($data[27]) ? $data[27] : null;

                if (empty($this->iniBasePrice)) $this->iniBasePrice = $this->iniRegularPrice;

                try {
                    $languageId = $this->languageRepository->getIdByCode($this->iniLocale);
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
                        'log' => "<span class='text-danger'>Error at line {$row}</span>: Not correct locale: {$this->iniLocale}",
                    ];
                    $invalidLines++;
                    continue;
                }

                if (!empty($this->iniProductVariantId) && strpos(strtolower($this->iniDeleteFull), 'delete') !== false) {
                    $this->productVariantRepository->delete($this->iniProductVariantId);
                    continue;
                }

                if (!empty($this->iniProductVariantId) && !empty($languageId) && strpos(strtolower($this->iniDeleteTranslation), 'delete') !== false) {
                    $this->productVariantTranslationRepository->deleteByVariantAndLanguageId($this->iniProductVariantId, $languageId);
                    continue;
                }

                $this->iniMediaId = $this->prepareBaseMedia($this->iniMediaId);
                if (empty($this->iniMediaId)) $this->iniMediaId = null;

                $this->iniBaseImgTranslated = $this->prepareBaseMedia($this->iniBaseImgTranslated);
                if (empty($this->iniBaseImgTranslated)) $this->iniBaseImgTranslated = null;

                $stockStatus = null;
                if ($this->iniStockStatus === 'in_stock') {
                    $stockStatus = 1;
                } else if ($this->iniStockStatus === 'out_of_stock') {
                    $stockStatus = 0;
                }

                $taxStatus = null;
                if ($this->iniTaxStatus === 'Taxable') {
                    $taxStatus = true;
                } else if ($this->iniTaxStatus === 'None') {
                    $taxStatus = false;
                }

                $status = null;
                if ($this->iniStatus === 'Active') {
                    $status = true;
                } else if ($this->iniStatus === 'Inactive') {
                    $status = false;
                }

                $gallery_ids = [];
                $galleryInserts = [];
                foreach ($this->iniGalleryIds as $galleryId) {
                    if (!empty($galleryId) && str_contains($galleryId, '~')) {
                        $videoInfo = explode('~', $galleryId);
                        $id = $this->prepareBaseMedia($videoInfo[0]);
                    } else {
                        $videoInfo = false;
                        $id = $this->prepareBaseMedia($galleryId);
                    }

                    if (!empty($id)) {
                        if (!$videoInfo) {
                            $videoType = null;
                            $videoUrl = null;
                        } else {
                            if (str_contains(strtolower($videoInfo[1]), 'youtube.') || str_contains(strtolower($videoInfo[1]), 'youtu.')) {
                                $videoType = 2;
                            } else {
                                $videoType = 1;
                            }
                            $videoUrl = $videoInfo[1];
                        }

                        $gallery_ids[] = $id;
                        $galleryInserts[] = [
                            'media_id' => $id,
                            'video_type' => $videoType,
                            'video_url' => $videoUrl,
                        ];
                    }
                }

                $gallery_ids_translation = [];
                $galleryInsertsTranslation = [];
                foreach ($this->iniGalleryIdsTranslated as $galleryId) {
                    if (!empty($galleryId) && str_contains($galleryId, '~')) {
                        $videoInfo = explode('~', $galleryId);
                        $id = $this->prepareBaseMedia($videoInfo[0]);
                    } else {
                        $videoInfo = false;
                        $id = $this->prepareBaseMedia($galleryId);
                    }

                    if (!empty($id)) {
                        if (!$videoInfo) {
                            $videoType = null;
                            $videoUrl = null;
                        } else {
                            if (str_contains(strtolower($videoInfo[1]), 'youtube.') || str_contains(strtolower($videoInfo[1]), 'youtu.')) {
                                $videoType = 2;
                            } else {
                                $videoType = 1;
                            }
                            $videoUrl = $videoInfo[1];
                        }

                        $gallery_ids_translation[] = $id;
                        $galleryInsertsTranslation[] = [
                            'media_id' => $id,
                            'video_type' => $videoType,
                            'video_url' => $videoUrl,
                        ];
                    }
                }

                DB::beginTransaction();

                try {
                    if (!$this->iniProductVariantId) {
                        $checkingAttrsIds = [];

                        if (preg_match('/ID:(\d+)/', $this->iniAttr1, $matches)) {
                            $checkingAttrsIds[] = $matches[1];
                        }

                        $uniqingArray = array_merge([$this->iniProductId], $checkingAttrsIds);
                        sort($uniqingArray);
                        $key = implode('_', $uniqingArray);

                        $this->iniProductVariantId = $this->productVariantRepository->getIdByKey($key);

                        if (!$this->iniProductVariantId) {
                            $mainSku = $this->productVariantRepository->getFieldByProductMainId($this->iniProductId, 'sku');

                            $productVariant = $this->productVariantRepository->create([
                                'product_id' => $this->iniProductId,
                                'key' => $key,
                                'is_main' => false,
                                'sku' => null,
                                'regular_price' => null,
                                'base_price' => 0,
                                'sales_price' => null,
                                'tax_status' => true,
                                'stock_status' => true,
                                'status' => false,
                            ]);

                            $this->iniProductVariantId = $productVariant->id;

                            $this->productVariantRepository->update('id', $productVariant->id, [
                                'sku' => "$mainSku-$productVariant->id"
                            ]);

                            $productVariantAttributes = [];

                            foreach ($checkingAttrsIds as $value) {
                                $productVariantAttributes[] = merge_dates_for_insert([
                                    'product_variant_id' => $productVariant->id,
                                    'attribute_id' => $value,
                                ], now());
                            }

                            if (!empty($productVariantAttributes)) $this->productVariantAttributeRepository->insert($productVariantAttributes);
                        }
                    }

                    $validator = $this->validate(
                        $stockStatus, $taxStatus, $status, $gallery_ids, $gallery_ids_translation
                    );

                    if (!$validator['success']) {
                        foreach ($validator['errors'] as $error) {
                            $logs[] = [
                                'upload_id' => $this->upload->id,
                                'log' => "<span class='text-danger'>Error at line {$row}</span>: {$error}",
                            ];
                        }
                        $invalidLines++;
                        DB::rollBack();
                        continue;
                    }

                    $productVariant = $this->productVariantRepository->fetchByField('id', $this->iniProductVariantId, 'id, product_id');

                    $productVariantPreparedArray = [
                        'sku' => $this->iniSku,
                        'media_id' => $this->iniMediaId,
                        'stock_status' => $stockStatus,
                        'regular_price' => $this->iniRegularPrice,
                        'base_price' => $this->iniBasePrice,
                        'sales_price' => $this->iniSalesPrice,
                        'tax_status' => $taxStatus,
                        'status' => $status,
                    ];

                    if (!empty($this->iniUnit) && !empty($this->iniSizeByUnit)) {
                        $productVariantPreparedArray['size_by_unit'] = [
                            'value' => $this->iniSizeByUnit,
                            'unit' => $this->iniUnit,
                        ];
                    } else {
                        $productVariantPreparedArray['size_by_unit'] = null;
                    }

                    $productVariantPreparedArray = $this->removeUnnecessaryProductVariantsTableArray($productVariantPreparedArray);

                    if (!empty($productVariantPreparedArray)) {
                        $this->productVariantRepository->update('id', $productVariant->id, $productVariantPreparedArray);
                    }

                    $translationArr = [
                        'media_id' => $this->iniBaseImgTranslated,
                        'name' => $this->iniName,
                        'short_description' => $this->iniShortDescription,
                        'description' => $this->iniDescription,
                    ];
                    $translationArr = $this->removeUnnecessaryProductVariantTranslationsTableArray($translationArr);
                    $variantTranslation = $this->productVariantTranslationRepository->updateOrCreate(
                        [
                            'product_variant_id' => $productVariant->id,
                            'language_id' => $languageId,
                        ],
                        $translationArr
                    );
                    if (!str_contains(strtoupper($this->fileHeaders['iniGalleryIdsTranslated']), 'SKIP')) {
                        $this->productVariantTranslationGalleryRepository->deleteByProductVariantTranslationId($variantTranslation->id);
                        if (!empty($galleryInsertsTranslation)) {
                            $galleryInsertArrayTranslation = [];
                            foreach ($galleryInsertsTranslation as $index => $galleryInsert) {
                                $galleryInsertArrayTranslation[] = merge_dates_for_insert([
                                    'product_variant_translation_id' => $variantTranslation->id,
                                    'media_id' => $galleryInsert['media_id'],
                                    'video_type' => $galleryInsert['video_type'],
                                    'video_url' => $galleryInsert['video_url'],
                                    'priority' => $index,
                                ], now());
                            }

                            if (!empty($galleryInsertArrayTranslation)) $this->productVariantTranslationGalleryRepository->insert($galleryInsertArrayTranslation);
                        }
                    }

                    if (!str_contains(strtoupper($this->fileHeaders['iniGalleryIds']), 'SKIP')) {
                        $this->productVariantGalleryRepository->deleteByProductVariantId($productVariant->id);

                        if (!empty($galleryInserts)) {
                            $galleryInsertArray = [];
                            foreach ($galleryInserts as $index => $galleryInsert) {
                                $galleryInsertArray[] = merge_dates_for_insert([
                                    'product_variant_id' => $productVariant->id,
                                    'media_id' => $galleryInsert['media_id'],
                                    'video_type' => $galleryInsert['video_type'],
                                    'video_url' => $galleryInsert['video_url'],
                                    'priority' => $index,
                                ], now());
                            }

                            if (!empty($galleryInsertArray)) $this->productVariantGalleryRepository->insert($galleryInsertArray);
                        }
                    }

                    if (!str_contains(strtoupper($this->fileHeaders['iniParentSKUs']), 'SKIP')) {
                        $skuParentsArray = $this->prepareParentSKUs($this->iniParentSKUs);
                        $onlyNewSKUs = array_column($skuParentsArray, 'sku');
                        $skus = $this->productVariantParentRepository->fetchSKUsByVariantId($productVariant->id);
                        $notActual = array_diff($skus, $onlyNewSKUs);

                        if (!empty($notActual)) $this->productVariantParentRepository->bulkDeleteBySKU($notActual, $productVariant->id);

                        foreach ($skuParentsArray as $skuParents) {
                            $itemId = $this->itemRepository->getIdBySKU($skuParents['sku']);
                            $this->productVariantParentRepository->updateOrCreateByItemId($skuParents['qty'], $productVariant->id, $itemId);
                        }
                    }

                    $this->productVariantCustomFieldTranslationRepository->deleteByParams($productVariant->id, $languageId);
                    for ($i = ($this->lastIndexWithoutMetaFields + 1); $i < count($data); $i++) {
                        if (empty($data[$i])) continue;
                        $keyNameForUpdateOrCreate = array_flip($this->customFieldsKeys)[$i];

                        $this->productVariantCustomFieldTranslationRepository->insert(merge_dates_for_insert([
                            'product_variant_id' => $productVariant->id,
                            'language_id' => $languageId,
                            'key' => $keyNameForUpdateOrCreate,
                            'value' => $data[$i],
                        ], now()));
                    }

                    if ($logsLoopNumber === 50) {
                        if (!empty($logs)) $this->uploadLogRepository->insert($logs);
                        $logsLoopNumber = 0;
                    }

                    if (!in_array($productVariant->product_id, $cacheClearingProducts)) {
                        $cacheClearingProducts[] = $productVariant->product_id;
                    }

                    Log::channel('product-upload-info')->info("Variant ID finished: {$this->iniProductVariantId}");

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
            }

            if (!empty($logs)) $this->uploadLogRepository->insert($logs);

            if (!empty($cacheClearingProducts)) {
                Artisan::call('update:variable-product-prices', [
                    'vendorKey' => $this->vendorKey,
                    'productIds' => $cacheClearingProducts
                ]);

                RemoveCache::dispatch($this->vendorKey, $cacheClearingProducts, -1);
            }

            $this->upload->update(
                [
                    'status' => UploadConstant::STATUSES['Completed'],
                    'total_lines' => $totalLines,
                    'invalid_lines' => $invalidLines,
                    'succeed_lines' => $totalLines - $invalidLines,
                ]
            );
            Log::channel('product-upload-info')->info("Import finished finished");

            broadcast(new ReloadPagePublic('update-uploads-page'));
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

    private function validate(
        ?int $stockStatus, ?bool $taxStatus, ?bool $status, ?array $gallery_ids, ?array $gallery_ids_translation
    ): array
    {
        $preparedValidationData = [
            'id' => $this->iniProductVariantId,
            'sku' => $this->iniSku,
            'media_id' => $this->iniMediaId,
            'media_id_translation' => $this->iniBaseImgTranslated,
            'stock_status' => $stockStatus,
            'regular_price' => $this->iniRegularPrice,
            'base_price' => $this->iniBasePrice,
            'sales_price' => $this->iniSalesPrice,
            'tax_status' => $taxStatus,
            'status' => $status,
            'gallery_ids' => $gallery_ids,
            'gallery_ids_translation' => $gallery_ids_translation,
            'parent_skus' => $this->iniParentSKUs,
            'size_by_unit' => $this->iniSizeByUnit,
        ];

        $rules = [
            'id' => 'required|integer|exists:product_variants,id',
            'media_id' => 'nullable|integer|exists:media,id',
            'media_id_translation' => 'nullable|integer|exists:media,id',
            'sku' => 'required|string|max:50|unique:product_variants,sku,' . $this->iniProductVariantId,
            'parent_skus' => ['required', 'string', new ValidParentSkusFormat(), new ValidParentSkusExist()],
            'regular_price' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
            'base_price' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
            'sales_price' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'size_by_unit' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'tax_status' => 'required|boolean',
            'stock_status' => 'required|boolean',
            'status' => 'required|boolean',
            'gallery_ids' => ['nullable', 'array'],
            'gallery_ids.*' => [
                'required',
                'integer',
                Rule::exists('media', 'id')
            ],
            'gallery_ids_translation' => ['nullable', 'array'],
            'gallery_ids_translation.*' => [
                'required',
                'integer',
                Rule::exists('media', 'id')
            ],
        ];

        $rules = $this->removeUnnecessaryKeysFromValidationArray($rules);
        Log::channel('product-upload-info')->info($rules);

        $validator = Validator::make($preparedValidationData, $rules);

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

    private function removeUnnecessaryKeysFromValidationArray(array $rules): array
    {
        if (str_contains(strtoupper($this->fileHeaders['iniMediaId']), 'SKIP')) {
            unset($rules['media_id']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniBaseImgTranslated']), 'SKIP')) {
            unset($rules['media_id_translation']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniSku']), 'SKIP')) {
            unset($rules['sku']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniParentSKUs']), 'SKIP')) {
            unset($rules['parent_skus']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniRegularPrice']), 'SKIP')) {
            unset($rules['regular_price']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniBasePrice']), 'SKIP')) {
            unset($rules['base_price']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniSalesPrice']), 'SKIP')) {
            unset($rules['sales_price']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniTaxStatus']), 'SKIP')) {
            unset($rules['tax_status']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniStockStatus']), 'SKIP')) {
            unset($rules['stock_status']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniStatus']), 'SKIP')) {
            unset($rules['status']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniSizeByUnit']), 'SKIP')) {
            unset($rules['size_by_unit']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniGalleryIds']), 'SKIP')) {
            unset($rules['gallery_ids']);
            unset($rules['gallery_ids.*']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniGalleryIdsTranslated']), 'SKIP')) {
            unset($rules['gallery_ids_translation']);
            unset($rules['gallery_ids_translation.*']);
        }

        return $rules;
    }

    private function removeUnnecessaryProductVariantTranslationsTableArray(array $array): array
    {
        if (str_contains(strtoupper($this->fileHeaders['iniBaseImgTranslated']), 'SKIP')) {
            unset($array['media_id']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniName']), 'SKIP')) {
            unset($array['name']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniShortDescription']), 'SKIP')) {
            unset($array['short_description']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniDescription']), 'SKIP')) {
            unset($array['description']);
        }

        return $array;
    }

    private function removeUnnecessaryProductVariantsTableArray(array $array): array
    {
        if (str_contains(strtoupper($this->fileHeaders['iniSku']), 'SKIP')) {
            unset($array['sku']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniMediaId']), 'SKIP')) {
            unset($array['media_id']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniStockStatus']), 'SKIP')) {
            unset($array['stock_status']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniBasePrice']), 'SKIP')) {
            unset($array['base_price']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniRegularPrice']), 'SKIP')) {
            unset($array['regular_price']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniSalesPrice']), 'SKIP')) {
            unset($array['sales_price']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniUnit']), 'SKIP') || str_contains(strtoupper($this->fileHeaders['iniSizeByUnit']), 'SKIP')) {
            unset($array['size_by_unit']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniTaxStatus']), 'SKIP')) {
            unset($array['tax_status']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniStatus']), 'SKIP')) {
            unset($array['status']);
        }

        return $array;
    }

    private function prepareParentSKUs(string $parentSKU): array
    {
        $parts = explode(',', $parentSKU);

        $result = [];

        foreach ($parts as $part) {
            list($qty, $sku) = explode('x', $part);

            $result[] = [
                'sku' => $sku,
                'qty' => (int)$qty,
            ];
        }

        return $result;
    }

    private function prepareBaseMedia($baseMedia)
    {
        $baseMedia = trim($baseMedia);
        if ($baseMedia && str_contains($baseMedia, 'http') && validate_url_for_download_image($baseMedia)) {
            $fileUrl = $baseMedia;
            $fileInfoArray = pathinfo(basename($fileUrl));

            $originalName = Str::slug($fileInfoArray['filename'], '-');

            if (!array_key_exists('extension', $fileInfoArray)) {
                Log::error("Image not found: " . $fileUrl);
                return null;
            }
            $originalExtension = $fileInfoArray['extension'];

            try {
                $newOriginalName = $originalName;

                $mediaExists = $this->mediaRepository->fetchByField('file_name', "$originalName.$originalExtension", 'id, file_name');
                if ($mediaExists) return $mediaExists->id;

                $file = $this->downloadImageFromUrl($fileUrl);
                if (empty($file)) return null;

                $fileSize = $file->getSize();
                $fileType = $file->getClientMimeType();

                $year = Carbon::now()->year;
                $month = Carbon::now()->month;

                if ($this->fileService->imagesSupportedTypes($fileType)) {
                    $originalPath = "/uploads/$this->vendorKey/images/{$year}/{$month}/";
                    $image = Image::make($file->getRealPath());

                    $width = $image->width();
                    $height = $image->height();
                    $mediaSetting = $this->mediaSettingRepository->fetch('id, name, width, height', [], [], [], [], []);
                    $path = "/{$year}/{$month}/" . $newOriginalName . '.webp';
                    $type = MediaConstants::TYPE_IMAGE;

                    foreach ($mediaSetting as $setting) {
                        $size = strtolower($setting->name);
                        $filePath = "/uploads/$this->vendorKey/{$type}/{$size}/{$year}/{$month}/";
                        $dimensions = [$setting->width, $setting->height];
                        $this->fileService->save($file, $dimensions, $newOriginalName, $filePath, $fileType);

                        if ($fileType != 'image/webp' && $size === 'maximum') {
                            $this->fileService->save($file, $dimensions, $newOriginalName, $originalPath, $fileType, $originalExtension);
                        }

                        if ($fileType === 'image/webp' && $size === 'maximum') {
                            $originalPath = "/uploads/$this->vendorKey/$type/$size/{$year}/{$month}/";
                        }
                    }
                } else {
                    $type = MediaConstants::TYPE_FILE;
                    if (str_starts_with($fileType, 'video/')) {
                        $type = MediaConstants::TYPE_VIDEO;
                    } elseif (str_starts_with($fileType, 'image/')) {
                        $type = MediaConstants::TYPE_IMAGE;
                    }

                    $originalPath = "/uploads/$this->vendorKey/$type/{$year}/{$month}/";
                    $this->fileService->save($file, [], $newOriginalName, $originalPath, $fileType, $originalExtension);
                }

                $now = now()->toDateTimeString();
                $mediaData = merge_dates_for_insert([
                    'user_id' => $this->userId,
                    'file_name' => $newOriginalName . '.' . $originalExtension,
                    'original_path' => $originalPath . $newOriginalName . '.' . $originalExtension,
                    'path' => $path ?? null,
                    'file_size' => $fileSize,
                    'width' => $width ?? null,
                    'height' => $height ?? null,
                    'file_type' => $fileType,
                    'type' => $type,
                ], $now);

                $media = $this->mediaRepository->create($mediaData);

                return $media->id;
            } catch (\Exception $exception) {
                return null;
            }
        } else {
            return $baseMedia;
        }
    }

    private function downloadImageFromUrl(string $url): ?UploadedFile
    {
        try {
            if ($this->urlExists($url)) {
                $imageContents = file_get_contents($url);
            } else {
                Log::error("Image not found: " . $url);
                return null; // or return a default image
            }
            $tempPath = tempnam(sys_get_temp_dir(), 'uploaded_file');
            file_put_contents($tempPath, $imageContents);

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $tempPath);
            finfo_close($finfo);

            return new UploadedFile($tempPath, basename($url), $mimeType, null, true);
        } catch (\Exception $exception) {
            return null;
        }
    }

    private function urlExists($url): bool
    {
        $headers = @get_headers($url);
        return strpos($headers[0], '404') === false;
    }

    private function prepareHeadersFile(array $headers): array
    {
        return [
            'iniProductVariantId' => $headers[0],
            'iniProductName' => $headers[1],
            'iniProductId' => $headers[2],
            'iniSku' => $headers[7],
            'iniParentSKUs' => $headers[8],
            'iniStockStatus' => $headers[9],
            'iniBasePrice' => $headers[10],
            'iniRegularPrice' => $headers[11],
            'iniSalesPrice' => $headers[12],
            'iniTaxStatus' => $headers[13],
            'iniStatus' => $headers[14],
            'iniMediaId' => $headers[15],
            'iniGalleryIds' => $headers[16],
            'iniDeleteFull' => $headers[17],
            'iniLocale' => $headers[18],
            'iniName' => $headers[19],
            'iniShortDescription' => $headers[20],
            'iniDescription' => $headers[21],
            'iniBaseImgTranslated' => $headers[22],
            'iniGalleryIdsTranslated' => $headers[23],
            'iniSizeByUnit' => $headers[24],
            'iniUnit' => $headers[25],
            'iniDeleteTranslation' => $headers[27]
        ];
    }
}
