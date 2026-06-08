<?php

namespace App\Imports\ERP;

use App\Constants\MediaConstants;
use App\Constants\ProductConstants;
use App\Constants\UploadConstant;
use App\Events\ReloadPagePublic;
use App\Jobs\Product\RemoveCache;
use App\Models\Attribute;
use App\Models\Item;
use App\Models\Language;
use App\Models\Media;
use App\Models\MediaSetting;
use App\Models\PageTranslation;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductCategory;
use App\Models\ProductRelatedProduct;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use App\Models\ProductVariantCustomFieldTranslation;
use App\Models\ProductVariantGallery;
use App\Models\ProductVariantParent;
use App\Models\ProductVariantReel;
use App\Models\ProductVariantShort;
use App\Models\ProductVariantTranslation;
use App\Models\ProductVariantTranslationGallery;
use App\Models\ProductVariantTranslationReel;
use App\Models\ProductVariantTranslationShort;
use App\Models\UploadLog;
use App\Repositories\Attribute\AttributeRepository;
use App\Repositories\Item\ItemRepository;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\Media\MediaRepository;
use App\Repositories\MediaSetting\MediaSettingRepository;
use App\Repositories\PageTranslation\PageTranslationRepository;
use App\Repositories\Product\ProductRepository;
use App\Repositories\ProductAttribute\ProductAttributeRepository;
use App\Repositories\ProductCategory\ProductCategoryRepository;
use App\Repositories\ProductRelatedProduct\ProductRelatedProductRepository;
use App\Repositories\ProductTranslation\ProductTranslationRepository;
use App\Repositories\ProductVariant\ProductVariantRepository;
use App\Repositories\ProductVariantCustomFieldTranslation\ProductVariantCustomFieldTranslationRepository;
use App\Repositories\ProductVariantGallery\ProductVariantGalleryRepository;
use App\Repositories\ProductVariantParent\ProductVariantParentRepository;
use App\Repositories\ProductVariantReel\ProductVariantReelRepository;
use App\Repositories\ProductVariantShort\ProductVariantShortRepository;
use App\Repositories\ProductVariantTranslation\ProductVariantTranslationRepository;
use App\Repositories\ProductVariantTranslationGallery\ProductVariantTranslationGalleryRepository;
use App\Repositories\ProductVariantTranslationReel\ProductVariantTranslationReelRepository;
use App\Repositories\ProductVariantTranslationShort\ProductVariantTranslationShortRepository;
use App\Repositories\UploadLog\UploadLogRepository;
use App\Rules\ValidParentSkusExist;
use App\Rules\ValidParentSkusFormat;
use App\Services\ERP\File\FileService;
use App\Services\General\CustomSlugService;
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

class ImportProducts implements ToCollection
{
    private $upload;
    private string $importType;
    private string $vendorKey;
    private array $customFieldsKeys = [];
    private array $cacheUpdateingProductIds = [];
    private int $lastIndexWithoutMetaFields = 0;
    private UploadLogRepository $uploadLogRepository;
    private ProductRepository $productRepository;
    private ProductAttributeRepository $productAttributeRepository;
    private ProductCategoryRepository $productCategoryRepository;
    private ProductRelatedProductRepository $productRelatedProductRepository;
    private ProductTranslationRepository $productTranslationRepository;
    private ProductVariantRepository $productVariantRepository;
    private ProductVariantCustomFieldTranslationRepository $productVariantCustomFieldTranslationRepository;
    private ProductVariantGalleryRepository $productVariantGalleryRepository;
    private ProductVariantTranslationGalleryRepository $productVariantTranslatedGalleryRepository;
    private LanguageRepository $languageRepository;
    private AttributeRepository $attributeRepository;
    private MediaRepository $mediaRepository;
    private FileService $fileService;
    private MediaSettingRepository $mediaSettingRepository;
    private ProductVariantParentRepository $productVariantParentRepository;
    private ItemRepository $itemRepository;
    private int $userId;

    private $iniSku;
    private $iniGtin;
    private $iniParents;
    private $iniWatermarkImg;
    private $iniBaseImg;
    private $iniProductType;
    private $iniStockStatus;
    private $iniBasePrice;
    private $iniRegularPrice;
    private $iniSalesPrice;
    private $iniTaxStatus;
    private $iniReviewAccess;
    private $iniIsExtra;
    private $iniVariantsExportIntoFeed;
    private $iniIsNew;
    private $iniIsBestseller;
    private $iniStatus;
    private $iniVariableAttrIds;
    private $iniSimpleAttrIds;
    private $iniCategoryIds;
    private $iniUpsellsIds;
    private $iniCrossSellsIds;
    private $iniRelatedIds;
    private $iniRelatedReviewerId;
    private $iniExtraProductIds;
    private $iniCalculatorId;
    private $iniGalleryIds;
    private $iniDeleteFull;
    private $iniLocale;
    private $iniPriority;
    private $iniSlug;
    private $iniName;
    private $iniSubName;
    private $iniShortDesc;
    private $iniDescription;
    private $iniWatermarkImgTranslated;
    private $iniBaseImgTranslated;
    private $iniGalleryIdsTranslated;
    private $iniMetaTitle;
    private $iniMetaKeywords;
    private $iniMetaDescription;
    private $iniSnippetId;
    private $iniAPlusContent;
    private $iniSecondAPlusContent;
    private $iniTranslationStatus;
    private $iniDeleteTranslation;

    private array $fileHeaders;
    private mixed $iniHSCode;
    private $iniReelIds;
    private $iniReelIdsTranslated;
    private $iniShortsUrls;
    private $iniShortsUrlsTranslated;

    private PageTranslationRepository $pageTranslationRepository;
    private ProductVariantTranslationRepository $productVariantTranslationRepository;
    private ProductVariantReelRepository $productVariantReelRepository;
    private ProductVariantTranslationReelRepository $productVariantTranslatedReelRepository;
    private ProductVariantShortRepository $productVariantShortRepository;
    private ProductVariantTranslationShortRepository $productVariantTranslatedShortRepository;

    public function __construct($upload, string $importType, string $vendorKey, int $userId)
    {
        Log::channel('product-upload-info')->info('__construct of ImportProducts');
        $this->upload = $upload;
        $this->importType = $importType;
        $this->vendorKey = $vendorKey;
        $this->userId = $userId;
        $this->uploadLogRepository = new UploadLogRepository(new UploadLog());
        $this->productRepository = new ProductRepository(new Product());
        $this->productAttributeRepository = new ProductAttributeRepository(new ProductAttribute());
        $this->productCategoryRepository = new ProductCategoryRepository(new ProductCategory());
        $this->productRelatedProductRepository = new ProductRelatedProductRepository(new ProductRelatedProduct());
        $this->productTranslationRepository = new ProductTranslationRepository(new ProductTranslation());
        $this->productVariantRepository = new ProductVariantRepository(new ProductVariant());
        $this->productVariantCustomFieldTranslationRepository = new ProductVariantCustomFieldTranslationRepository(new ProductVariantCustomFieldTranslation());
        $this->productVariantGalleryRepository = new ProductVariantGalleryRepository(new ProductVariantGallery());
        $this->productVariantTranslatedGalleryRepository = new ProductVariantTranslationGalleryRepository(new ProductVariantTranslationGallery());
        $this->productVariantTranslationRepository = new ProductVariantTranslationRepository(new ProductVariantTranslation());
        $this->languageRepository = new LanguageRepository(new Language());
        $this->attributeRepository = new AttributeRepository(new Attribute());
        $this->mediaRepository = new MediaRepository(new Media());
        $this->fileService = new FileService();
        $this->mediaSettingRepository = new MediaSettingRepository(new MediaSetting());
        $this->productVariantParentRepository = new ProductVariantParentRepository(new ProductVariantParent());
        $this->itemRepository = new ItemRepository(new Item());
        $this->pageTranslationRepository = new PageTranslationRepository(new PageTranslation());
        $this->productVariantReelRepository = new ProductVariantReelRepository(new ProductVariantReel());
        $this->productVariantTranslatedReelRepository = new ProductVariantTranslationReelRepository(new ProductVariantTranslationReel());
        $this->productVariantShortRepository = new ProductVariantShortRepository(new ProductVariantShort());
        $this->productVariantTranslatedShortRepository = new ProductVariantTranslationShortRepository(new ProductVariantTranslationShort());
    }

    public function collection(Collection $collection): void
    {
        Log::channel('product-upload-info')->info('Start of ImportProducts');
        $invalidLines = 0;
        $logs = [];
        $totalLines = 0;

        setDBConnection($this->vendorKey);
        try {
            foreach ($collection as $row => $data) {
                $data = $data->toArray();
                if ($row == 0) {
                    $lastIndexFound = false;
                    foreach ($data as $index => $value) {
                        if (empty($value) || !str_starts_with($value, 'Meta:')) {
                            if (!$lastIndexFound) {
                                $this->lastIndexWithoutMetaFields = $index;
                            }
                            continue;
                        } else {
                            $lastIndexFound = true;
                        }

                        $customFieldKey = trim(substr(trim($value), 5));
                        $this->customFieldsKeys[$customFieldKey] = $index;
                    }

                    $this->fileHeaders = $this->prepareHeadersFile($data);
                    if ($this->lastIndexWithoutMetaFields + 1 !== count(UploadConstant::PRODUCTS_FILE_HEADERS)) {
                        throw new \Exception('Columns count is wrong');
                    }

                    continue;
                }

                $totalLines ++;

                $this->iniSku = !empty($data[0]) ? $data[0] : null;
                $this->iniGtin = !empty($data[1]) ? $data[1] : null;
                $this->iniParents = !empty($data[2]) ? $data[2] : null;
                $this->iniWatermarkImg = !empty($data[3]) ? $data[3] : null;
                $this->iniBaseImg = !empty($data[4]) ? $data[4] : null;
                $this->iniProductType = $data[5];
                $this->iniStockStatus = $data[6];
                $this->iniBasePrice = !empty($data[7]) ? str_replace(',', '.', $data[7]) : null;
                $this->iniRegularPrice = !empty($data[8]) ? str_replace(',', '.', $data[8]) : null;
                $this->iniSalesPrice = !empty($data[9]) ? str_replace(',', '.', $data[9]) : null;
                $this->iniTaxStatus = !empty($data[10]) ? $data[10] : null;
                $this->iniReviewAccess = !empty($data[11]) ? $data[11] : null;
                $this->iniIsExtra = !empty($data[12]) ? $data[12] : null;
                $this->iniVariantsExportIntoFeed = !empty($data[13]) ? $data[13] : null;
                $this->iniIsNew = !empty($data[14]) ? $data[14] : null;
                $this->iniIsBestseller = !empty($data[15]) ? $data[15] : null;
                $this->iniStatus = !empty($data[16]) ? $data[16] : null;
                $this->iniVariableAttrIds = !empty($data[17]) ? $data[17] : null;
                $this->iniSimpleAttrIds = !empty($data[18]) ? $data[18] : null;
                $this->iniCategoryIds = !empty($data[19]) ? explode(',', $data[19]) : [];
                $this->iniUpsellsIds = !empty($data[20]) ? explode(',', $data[20]) : [];
                $this->iniCrossSellsIds = !empty($data[21]) ? explode(',', $data[21]) : [];
                $this->iniRelatedIds = !empty($data[22]) ? explode(',', $data[22]) : [];
                $this->iniRelatedReviewerId = !empty($data[23]) ? $data[23] : null;
                $this->iniExtraProductIds = !empty($data[24]) ? explode(',', $data[24]) : [];
                $this->iniCalculatorId = !empty($data[25]) ? $data[25] : null;
                $this->iniGalleryIds = !empty($data[26]) ? explode(',', $data[26]) : [];
                $this->iniPriority = !empty($data[27]) ? $data[27] : 0;
                $this->iniHSCode = !empty($data[28]) ? $data[28] : null;
                $this->iniReelIds = !empty($data[29]) ? explode(',', $data[29]) : [];
                $this->iniShortsUrls = !empty($data[30]) ? $data[30] : null;
                $this->iniDeleteFull = !empty($data[31]) ? $data[31] : null;
                $this->iniLocale = !empty($data[32]) ? $data[32] : null;
                $this->iniSlug = !empty($data[33]) ? $data[33] : null;
                $this->iniName = !empty($data[34]) ? $data[34] : null;
                $this->iniSubName = !empty($data[35]) ? $data[35] : null;
                $this->iniShortDesc = !empty($data[36]) ? $data[36] : null;
                $this->iniDescription = !empty($data[37]) ? replace_h1_tags($data[37]) : null;
                $this->iniWatermarkImgTranslated = !empty($data[38]) ? $data[38] : null;
                $this->iniBaseImgTranslated = !empty($data[39]) ? $data[39] : null;
                $this->iniGalleryIdsTranslated = !empty($data[40]) ? explode(',', $data[40]) : [];
                $this->iniMetaTitle = !empty($data[41]) ? $data[41] : null;
                $this->iniMetaKeywords = !empty($data[42]) ? $data[42] : null;
                $this->iniMetaDescription = !empty($data[43]) ? $data[43] : null;
                $this->iniSnippetId = !empty($data[44]) ? $data[44] : null;
                $this->iniAPlusContent = !empty($data[45]) ? $data[45] : null;
                $this->iniSecondAPlusContent = !empty($data[46]) ? $data[46] : null;
                $this->iniTranslationStatus = !empty($data[47]) ? $data[47] : null;
                $this->iniReelIdsTranslated = !empty($data[48]) ? explode(',', $data[48]) : [];
                $this->iniShortsUrlsTranslated = !empty($data[49]) ? $data[49] : null;
                $this->iniDeleteTranslation = !empty($data[50]) ? $data[50] : null;

                if (!empty($this->iniRelatedReviewerId)) {
                    $this->iniRelatedReviewerId = $this->productVariantRepository->getProductIdBySKU($this->iniRelatedReviewerId);
                }

                if (empty($this->iniBasePrice)) $this->iniBasePrice = $this->iniRegularPrice;

                if ($this->iniCrossSellsIds) {
                    $this->iniCrossSellsIds = array_map('trim', $this->iniCrossSellsIds);
                    $this->iniCrossSellsIds = $this->productVariantRepository->getProductIdsBySKUs($this->iniCrossSellsIds);
                }

                if ($this->iniUpsellsIds) {
                    $this->iniUpsellsIds = array_map('trim', $this->iniUpsellsIds);
                    $this->iniUpsellsIds = $this->productVariantRepository->getProductIdsBySKUs($this->iniUpsellsIds);
                }

                if ($this->iniRelatedIds) {
                    $this->iniRelatedIds = array_map('trim', $this->iniRelatedIds);
                    $this->iniRelatedIds = $this->productVariantRepository->getProductIdsBySKUs($this->iniRelatedIds);
                }

                if ($this->iniExtraProductIds) {
                    $this->iniExtraProductIds = array_map('trim', $this->iniExtraProductIds);
                    $this->iniExtraProductIds = $this->productVariantRepository->getProductIdsBySKUs($this->iniExtraProductIds);
                }

                $this->iniBaseImg = $this->prepareBaseMedia($this->iniBaseImg);
                if (empty($this->iniBaseImg)) $this->iniBaseImg = null;

                $this->iniWatermarkImg = $this->prepareBaseMedia($this->iniWatermarkImg);
                if (empty($this->iniWatermarkImg)) $this->iniWatermarkImg = null;

                $this->iniBaseImgTranslated = $this->prepareBaseMedia($this->iniBaseImgTranslated);
                if (empty($this->iniBaseImgTranslated)) $this->iniBaseImgTranslated = null;

                $this->iniWatermarkImgTranslated = $this->prepareBaseMedia($this->iniWatermarkImgTranslated);
                if (empty($this->iniWatermarkImgTranslated)) $this->iniWatermarkImgTranslated = null;

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

                if (!empty($this->iniSnippetId)) {
                    $this->iniSnippetId = $this->pageTranslationRepository->getIdByPageIdAndLanguageId($this->iniSnippetId, $languageId);
                }

                if (!empty($this->iniAPlusContent)) {
                    $this->iniAPlusContent = $this->pageTranslationRepository->getIdByPageIdAndLanguageId($this->iniAPlusContent, $languageId);
                }

                if (!empty($this->iniSecondAPlusContent)) {
                    $this->iniSecondAPlusContent = $this->pageTranslationRepository->getIdByPageIdAndLanguageId($this->iniSecondAPlusContent, $languageId);
                }

                $product = null;
                if (!empty($this->iniSku)) {
                    $product = $this->productRepository->findBySKUForUploadProducts($this->iniSku, $languageId);
                }

                if (strpos(strtolower($this->iniDeleteFull), 'delete') !== false) {
                    if ($product) {
                        $this->productRepository->delete($product->id);
                    }
                    continue;
                }

                if (strpos(strtolower($this->iniDeleteTranslation), 'delete') !== false) {
                    if ($product && $product->product_translation) {
                        $this->productTranslationRepository->delete($product->product_translation->id);
                    }
                    continue;
                }

                $productType = null;
                if ($this->iniProductType === 'simple') {
                    $productType = 0;
                } else if ($this->iniProductType === 'variable') {
                    $productType = 1;
                } else if ($this->iniProductType === 'bundle') {
                    $productType = 2;
                } else if ($this->iniProductType === 'gift_card') {
                    $productType = 3;
                }

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

                $reviewsAccess = null;
                if ($this->iniReviewAccess === 'Enabled') {
                    $reviewsAccess = true;
                } else if ($this->iniReviewAccess === 'Disabled') {
                    $reviewsAccess = false;
                }

                $isNew = null;
                if ($this->iniIsNew === 'Yes') {
                    $isNew = true;
                } else if ($this->iniIsNew === 'No') {
                    $isNew = false;
                }

                $isExtra = null;
                if ($this->iniIsExtra === 'Yes') {
                    $isExtra = true;
                } else if ($this->iniIsExtra === 'No') {
                    $isExtra = false;
                }

                $isVariantsExportIntoFeed = null;
                if ($this->iniVariantsExportIntoFeed === 'Yes') {
                    $isVariantsExportIntoFeed = true;
                } else if ($this->iniVariantsExportIntoFeed === 'No') {
                    $isVariantsExportIntoFeed = false;
                }

                $isBestseller = null;
                if ($this->iniIsBestseller === 'Yes') {
                    $isBestseller = true;
                } else if ($this->iniIsBestseller === 'No') {
                    $isBestseller = false;
                }

                $status = null;
                if ($this->iniStatus === 'Active') {
                    $status = true;
                } else if ($this->iniStatus === 'Inactive') {
                    $status = false;
                }

                $translationStatus = null;
                if ($this->iniTranslationStatus === 'Active') {
                    $translationStatus = true;
                } else if ($this->iniTranslationStatus === 'Inactive') {
                    $translationStatus = false;
                }

                $category_ids = $this->iniCategoryIds;
                $upsells_ids = $this->iniUpsellsIds;
                $cross_sells_ids = $this->iniCrossSellsIds;
                $extra_products_ids = $this->iniExtraProductIds;
                $related_ids = $this->iniRelatedIds;
                $gallery_ids = [];
                $galleryInserts = [];
                $gallery_ids_translated = [];
                $galleryInsertsTranslated = [];
                $reel_ids = [];
                $reelInserts = [];
                $reel_ids_translated = [];
                $reelInsertsTranslated = [];

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

                        $gallery_ids_translated[] = $id;
                        $galleryInsertsTranslated[] = [
                            'media_id' => $id,
                            'video_type' => $videoType,
                            'video_url' => $videoUrl,
                        ];
                    }
                }

                $this->processReelIds($this->iniReelIds, $reel_ids, $reelInserts);
                $this->processReelIds($this->iniReelIdsTranslated, $reel_ids_translated, $reelInsertsTranslated);

                $validator = $this->validate(
                    $product?->product_variant_main, $productType, $stockStatus, $taxStatus,
                    $reviewsAccess, $isNew, $isExtra, $isVariantsExportIntoFeed, $isBestseller, $status, $translationStatus, $category_ids,
                    $upsells_ids, $cross_sells_ids, $extra_products_ids, $related_ids, $gallery_ids, $gallery_ids_translated,
                    $reel_ids, $reel_ids_translated
                );

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

                DB::beginTransaction();
                try {
                    $productPreparedArray = [
                        'calculator_id' => $this->iniCalculatorId,
                        'type' => $productType,
                        'enable_reviews' => $reviewsAccess,
                        'new' => $isNew,
                        'extra_product' => $isExtra,
                        'variants_export_into_feed' => $isVariantsExportIntoFeed,
                        'bestseller' => $isBestseller,
                        'related_reviewer_id' => $this->iniRelatedReviewerId,
                        'priority' => $this->iniPriority,
                        'hs_code' => $this->iniHSCode,
                        'gtin' => $this->iniGtin,
                        'watermark_settings' => $this->iniWatermarkImg ? [
                            'watermark_media' => $this->mediaRepository->fetchByField('id', $this->iniWatermarkImg, 'id, original_path')->original_path,
                            'watermark_height' => '700',
                            'watermark_position' => 'center',
                            'watermark_x' => '0',
                            'watermark_y' => '0',
                        ] : null,
                    ];

                    if ($this->importType == 2 || ($this->importType == 3 && !empty($product?->product_variant_main))) {
                        $productPreparedArray = $this->removeUnnecessaryProductsTableArray($productPreparedArray);
                    }

                    $productMainVariantPreparedArray = [
                        'is_main' => true,
                        'sku' => $this->iniSku,
                        'media_id' => $this->iniBaseImg,
                        'stock_status' => $stockStatus,
                        'base_price' => $this->iniBasePrice,
                        'regular_price' => $this->iniRegularPrice,
                        'sales_price' => $this->iniSalesPrice,
                        'tax_status' => $taxStatus,
                        'status' => $status,
                    ];

                    if ($this->importType == 2 || ($this->importType == 3 && !empty($product?->product_variant_main))) {
                        $productMainVariantPreparedArray = $this->removeUnnecessaryProductVariantsTableArray($productMainVariantPreparedArray);
                    }

                    if ($product) {
                        $productVariant = $product->product_variant_main;
                    }

                    if (!empty($productMainVariantPreparedArray) && (!empty($productPreparedArray) || $product)) {
                        if (!$product) {
                            $product = $this->productRepository->create($productPreparedArray);
                            $productMainVariantPreparedArray['product_id'] = $product->id;
                            $productVariant = $this->productVariantRepository->create($productMainVariantPreparedArray);
                        } else {
                            $this->productRepository->update('id', $product->id, $productPreparedArray);
                            $this->productVariantRepository->update('id', $productVariant->id, $productMainVariantPreparedArray);
                        }
                    }

                    Artisan::call('product:remove-cache', [
                        'productIds' => [$product->id],
                        'languageId' => $languageId,
                        'vendorKey' => $this->vendorKey
                    ]);

                    if (
                        ($this->importType == 2 || ($this->importType == 3 && !empty($product?->product_variant_main))) &&
                        !str_contains(strtoupper($this->fileHeaders['iniParents']), 'SKIP') &&
                        !empty($this->iniParents)
                    ) {
                        $skuParentsArray = $this->prepareParentSKUs($this->iniParents);
                        $onlyNewSKUs = array_column($skuParentsArray, 'sku');
                        $skus = $this->productVariantParentRepository->fetchSKUsByVariantId($productVariant->id);
                        $notActual = array_diff($skus, $onlyNewSKUs);

                        if (!empty($notActual)) $this->productVariantParentRepository->bulkDeleteBySKU($notActual, $productVariant->id);

                        foreach ($skuParentsArray as $skuParents) {
                            $itemId = $this->itemRepository->getIdBySKU($skuParents['sku']);
                            $this->productVariantParentRepository->updateOrCreateByItemId($skuParents['qty'], $productVariant->id, $itemId);
                        }
                    }

                    $productTranslationPreparedArray = [
                        'product_id' => $product->id,
                        'language_id' => $languageId,
                        'translation_status' => $translationStatus,
                        'slug' => $this->iniSlug,
                        'name' => $this->iniName,
                        'sub_name' => $this->iniSubName,
                        'short_description' => $this->iniShortDesc,
                        'description' => $this->iniDescription,
                        'meta_title' => $this->iniMetaTitle,
                        'meta_keywords' => $this->iniMetaKeywords,
                        'meta_description' => $this->iniMetaDescription,
                        'a_plus_content_id' => $this->iniAPlusContent,
                        'sec_a_plus_content_id' => $this->iniSecondAPlusContent,
                        'snippet_id' => $this->iniSnippetId,
                        'watermark_settings' => $this->iniWatermarkImgTranslated ? [
                            'watermark_media' => $this->mediaRepository->fetchByField('id', $this->iniWatermarkImgTranslated, 'id, original_path')->original_path,
                            'watermark_height' => '700',
                            'watermark_position' => 'center',
                            'watermark_x' => '0',
                            'watermark_y' => '0',
                        ] : null,
                    ];

                    $productTranslationTranslation = $this->productTranslationRepository->fetchByProductAndLanguageId($product->id, $languageId, 'id, slug');

                    if (!str_contains(strtoupper($this->fileHeaders['iniSlug']), 'SKIP')) {
                        if ($productTranslationTranslation) {
                            if ($productTranslationTranslation->slug !== $this->iniSlug || empty($this->iniSlug)) {
                                $slugValue = !empty($this->iniSlug) ? $this->iniSlug : $this->iniName;
                                $slug = CustomSlugService::createCustomSlug(
                                    new ProductTranslation(),
                                    $slugValue,
                                    $languageId
                                );
                            } else {
                                $slug = $this->iniSlug;
                            }
                        } else {
                            $slugValue = !empty($this->iniSlug) ? $this->iniSlug : $this->iniName;
                            $slug = CustomSlugService::createCustomSlug(
                                new ProductTranslation(),
                                $slugValue,
                                $languageId
                            );
                        }

                        $productTranslationPreparedArray['slug'] = $slug;
                    }

                    if ($this->importType == 2 || ($this->importType == 3 && !empty($product?->product_variant_main))) {
                        $productTranslationPreparedArray = $this->removeUnnecessaryProductTranslationsArray($productTranslationPreparedArray);
                    }

                    if (!empty($productTranslationPreparedArray)) {
                        if ($this->importType == 1 && !$productTranslationTranslation) {
                            $this->productTranslationRepository->create($productTranslationPreparedArray);
                        } else if ($this->importType == 2 || $this->importType == 3) {
                            if (!$productTranslationTranslation && isset($productTranslationPreparedArray['slug'])) {
                                $this->productTranslationRepository->create($productTranslationPreparedArray);
                            } else {
                                unset($productTranslationPreparedArray['slug']);
                                unset($productTranslationPreparedArray['path']);
                                $this->productTranslationRepository->update('id', $productTranslationTranslation->id, $productTranslationPreparedArray);
                            }
                        }
                    }

                    if (
                        ($this->importType == 2 || ($this->importType == 3 && !empty($product?->product_variant_main))) &&
                        !str_contains(strtoupper($this->fileHeaders['iniRelatedIds']), 'SKIP')
                    ) {
                        // relateds part
                        $productWithRelateds = $this->productRepository->fetchRelatedsByProductId($product->id);
                        $prepareForNewRelatedsArray = [];
                        $relatedsToRemove = [];
                        $relatedsArrayWithKeys = [
                            'upsells_product_ids' => $upsells_ids,
                            'cross_sells_product_ids' => $cross_sells_ids,
                            'extra_products_product_ids' => $extra_products_ids,
                            'related_product_ids' => $related_ids
                        ];

                        foreach (ProductConstants::RELATED_PRODUCT_TYPES as $type => $value) {
                            if ("{$type}_product_ids" === 'bundling_product_ids') continue;

                            $currentRelated = $productWithRelateds->$type->pluck('related_product_id')->toArray();
                            $newIds = array_map('intval', $relatedsArrayWithKeys["{$type}_product_ids"]);
                            $relatedToAdd = array_diff($newIds, $currentRelated);

                            foreach ($relatedToAdd as $relatedId) {
                                $prepareForNewRelatedsArray[] = merge_dates_for_insert([
                                    'product_id' => $product->id,
                                    'related_product_id' => $relatedId,
                                    'type' => ProductConstants::RELATED_PRODUCT_TYPES[$type]
                                ], now());
                            }

                            $toRemove = array_diff($currentRelated, $newIds);
                            $relatedsToRemove = array_unique(array_merge($relatedsToRemove, $toRemove));
                        }

                        if (!empty($prepareForNewRelatedsArray)) $this->productRelatedProductRepository->insert($prepareForNewRelatedsArray);
                        if (!empty($relatedsToRemove)) $this->productRelatedProductRepository->deleteByProductAndRelatedProductIds($product->id, $relatedsToRemove);
                    }

                    if (
                        ($this->importType == 2 || ($this->importType == 3 && !empty($product?->product_variant_main))) &&
                        !str_contains(strtoupper($this->fileHeaders['iniVariableAttrIds']), 'SKIP')
                    ) {
                        $attributeIds = [];
                        $workingAttributeIds = [];
                        if (!empty($this->iniVariableAttrIds) && $productType == ProductConstants::TYPES['variable']) {
                            $attributeIds = explode(',', $this->iniVariableAttrIds);
                        }
                        if (!empty($this->iniSimpleAttrIds)) {
                            $attributeIds = array_merge($attributeIds, explode(',', $this->iniSimpleAttrIds));
                        }

                        foreach ($attributeIds as $attributeId) {
                            $id = (int)trim($attributeId);
                            if (empty($id)) continue;
                            $workingAttributeIds[] = $id;
                        }

                        $currentAttributes = $this->productAttributeRepository->fetchIdsByProductId($product->id);
                        $attributesToAdd = array_diff($workingAttributeIds, $currentAttributes);

                        $issueExists = false;
                        foreach ($attributesToAdd as $id) {
                            $attributeExists = $this->attributeRepository->existsById($id);

                            if (!$attributeExists) {
                                $issueExists = true;
                                $logs[] = [
                                    'upload_id' => $this->upload->id,
                                    'log' => "<span class='text-danger'>Error at line {$row}</span>: Invalid attribute value ID",
                                ];
                            } else {
                                $this->productAttributeRepository->insert(merge_dates_for_insert([
                                    'product_id' => $product->id,
                                    'attribute_id' => $id
                                ], now()));
                            }
                        }

                        $attributesToRemove = array_diff($currentAttributes, $workingAttributeIds);
                        if (!empty($attributesToRemove)) {
                            $this->productAttributeRepository->deleteByProductAndAttributeIds($product->id, $attributesToRemove);
                            $this->productVariantRepository->deleteByProductIdAndAttributeIds($product->id, $attributesToRemove);
                        }

                        if ($issueExists) $invalidLines++;
                    }

                    if (
                        ($this->importType == 2 || ($this->importType == 3 && !empty($product?->product_variant_main))) &&
                        !str_contains(strtoupper($this->fileHeaders['iniCategoryIds']), 'SKIP')
                    ) {
                        $this->productCategoryRepository->updatePrimaryStatus($product->id);

                        $currentCategories = $this->productCategoryRepository->fetchIdsByProductId($product->id);
                        $category_ids = array_map('intval', $category_ids);
                        $categoriesToAdd = array_diff($category_ids, $currentCategories);
                        $prepareForNewCategoriesArray = [];

                        foreach ($categoriesToAdd as $categoryId) {
                            $prepareForNewCategoriesArray[] = merge_dates_for_insert([
                                'product_id' => $product->id,
                                'category_id' => $categoryId,
                            ], now());
                        }

                        if (!empty($prepareForNewCategoriesArray)) $this->productCategoryRepository->insert($prepareForNewCategoriesArray);

                        $categoriesToRemove = array_diff($currentCategories, $category_ids);
                        if (!empty($categoriesToRemove)) $this->productCategoryRepository->deleteByProductAndCategoryIds($product->id, $categoriesToRemove);

                        $this->productCategoryRepository->updatePrimaryStatusByCategoryId($product->id, $category_ids[0]);
                    }

                    if (
                        ($this->importType == 2 || ($this->importType == 3 && !empty($product?->product_variant_main))) &&
                        !str_contains(strtoupper($this->fileHeaders['iniGalleryIds']), 'SKIP')
                    ) {
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

                    $productMainVariantTranslationPreparedArray = [
                        'media_id' => $this->iniBaseImgTranslated,
                    ];

                    if ($this->importType == 2 || ($this->importType == 3 && !empty($product?->product_variant_main))) {
                        $productMainVariantTranslationPreparedArray = $this->removeUnnecessaryProductVariantTranslationsTableArray($productMainVariantTranslationPreparedArray);
                    }

                    $variantMainTranslation = $this->productVariantTranslationRepository->updateOrCreate(
                        [
                            'product_variant_id' => $productVariant->id,
                            'language_id' => $languageId,
                        ],
                        $productMainVariantTranslationPreparedArray
                    );

                    if (
                        ($this->importType == 2 || ($this->importType == 3 && !empty($product?->product_variant_main))) &&
                        !str_contains(strtoupper($this->fileHeaders['iniGalleryIdsTranslated']), 'SKIP')
                    ) {
                        $this->productVariantTranslatedGalleryRepository->deleteByProductVariantTranslationId($variantMainTranslation->id);

                        if (!empty($galleryInsertsTranslated)) {
                            $galleryTranslatedInsertArray = [];
                            foreach ($galleryInsertsTranslated as $index => $galleryInsert) {
                                $galleryTranslatedInsertArray[] = merge_dates_for_insert([
                                    'product_variant_translation_id' => $variantMainTranslation->id,
                                    'media_id' => $galleryInsert['media_id'],
                                    'video_type' => $galleryInsert['video_type'],
                                    'video_url' => $galleryInsert['video_url'],
                                    'priority' => $index,
                                ], now());
                            }

                            if (!empty($galleryTranslatedInsertArray)) $this->productVariantTranslatedGalleryRepository->insert($galleryTranslatedInsertArray);
                        }
                    }

                    if (!empty($this->customFieldsKeys)) {
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
                    }

                    if (
                        ($this->importType == 2 || ($this->importType == 3 && !empty($product?->product_variant_main))) &&
                        !str_contains(strtoupper($this->fileHeaders['iniReelIds']), 'SKIP')
                    ) {
                        $this->productVariantReelRepository->deleteByProductVariantId($productVariant->id);

                        if (!empty($reelInserts)) {
                            $reelInsertArray = [];
                            foreach ($reelInserts as $index => $reelInsert) {
                                $reelInsertArray[] = merge_dates_for_insert([
                                    'product_variant_id' => $productVariant->id,
                                    'media_id' => $reelInsert['media_id'],
                                    'video_type' => $reelInsert['video_type'],
                                    'video_url' => $reelInsert['video_url'],
                                    'priority' => $index,
                                ], now());
                            }

                            if (!empty($reelInsertArray)) $this->productVariantReelRepository->insert($reelInsertArray);
                        }
                    }

                    if (
                        ($this->importType == 2 || ($this->importType == 3 && !empty($product?->product_variant_main))) &&
                        !str_contains(strtoupper($this->fileHeaders['iniReelIdsTranslated']), 'SKIP')
                    ) {
                        $this->productVariantTranslatedReelRepository->deleteByProductVariantTranslationId($variantMainTranslation->id);

                        if (!empty($reelInsertsTranslated)) {
                            $reelTranslatedInsertArray = [];
                            foreach ($reelInsertsTranslated as $index => $reelInsert) {
                                $reelTranslatedInsertArray[] = merge_dates_for_insert([
                                    'product_variant_translation_id' => $variantMainTranslation->id,
                                    'media_id' => $reelInsert['media_id'],
                                    'video_type' => $reelInsert['video_type'],
                                    'video_url' => $reelInsert['video_url'],
                                    'priority' => $index,
                                ], now());
                            }

                            if (!empty($reelTranslatedInsertArray)) $this->productVariantTranslatedReelRepository->insert($reelTranslatedInsertArray);
                        }
                    }

                    if (
                        ($this->importType == 2 || ($this->importType == 3 && !empty($product?->product_variant_main))) &&
                        !str_contains(strtoupper($this->fileHeaders['iniShortsUrls']), 'SKIP')
                    ) {
                        $this->productVariantShortRepository->updateOrCreate(
                            ['product_variant_id' => $productVariant->id],
                            ['shorts_urls' => $this->iniShortsUrls]
                        );
                    }

                    if (
                        ($this->importType == 2 || ($this->importType == 3 && !empty($product?->product_variant_main))) &&
                        !str_contains(strtoupper($this->fileHeaders['iniShortsUrlsTranslated']), 'SKIP')
                    ) {
                        $this->productVariantTranslatedShortRepository->updateOrCreate(
                            ['product_variant_translation_id' => $variantMainTranslation->id],
                            ['shorts_urls' => $this->iniShortsUrlsTranslated]
                        );
                    }

                    $this->cacheUpdateingProductIds[] = $product->id;

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
            if (!empty($this->cacheUpdateingProductIds)) RemoveCache::dispatch($this->vendorKey, $this->cacheUpdateingProductIds, -1);

            $this->upload->update([
                'status' => UploadConstant::STATUSES['Completed'],
                'total_lines' => $totalLines,
                'invalid_lines' => $invalidLines,
                'succeed_lines' => $totalLines - $invalidLines,
            ]);

            broadcast(new ReloadPagePublic('update-uploads-page'));
            broadcast(new ReloadPagePublic('update-products-page'));

            Log::channel('product-upload-info')->info("Import finished finished ImportProducts");

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
        $productVariant, ?int $productType, ?int $stockStatus, ?bool $taxStatus, ?bool $reviewsAccess, ?bool $isNew, ?bool $isExtra, ?bool $isVariantsExportIntoFeed,
        ?bool $isBestseller, ?bool $status,  ?bool $translationStatus, ?array $category_ids,
        ?array $upsells_ids, ?array $cross_sells_ids, ?array $extra_products_ids, ?array $related_ids, ?array $gallery_ids, ?array $gallery_ids_translated,
        ?array $reel_ids, ?array $reel_ids_translated): array
    {
        if ($this->importType == 2 && empty($productVariant)) {
            return [
                'success' => false,
                'errors' => ["There is not product by this SKU"],
            ];
        } else if ($this->importType == 1 && !empty($productVariant)) {
            return [
                'success' => false,
                'errors' => ["Product by this SKU already exists"],
            ];
        }

        $preparedValidationData = [
            'sku' => $this->iniSku,
            'gtin' => $this->iniGtin,
            'parent_skus' => $this->iniParents,
            'media_id' => $this->iniBaseImg,
            'media_id_translated' => $this->iniBaseImgTranslated,
            'type' => $productType,
            'stock_status' => $stockStatus,
            'base_price' => $this->iniBasePrice,
            'regular_price' => $this->iniRegularPrice,
            'sales_price' => $this->iniSalesPrice,
            'related_reviewer_id' => $this->iniRelatedReviewerId,
            'priority' => $this->iniPriority,
            'calculator_id' => $this->iniCalculatorId,
            'tax_status' => $taxStatus,
            'enable_reviews' => $reviewsAccess,
            'new' => $isNew,
            'extra_product' => $isExtra,
            'variants_export_into_feed' => $isVariantsExportIntoFeed,
            'bestseller' => $isBestseller,
            'status' => $status,
            'translation_status' => $translationStatus,
            'category_ids' => $category_ids,
            'upsells_product_ids' => $upsells_ids,
            'cross_sells_product_ids' => $cross_sells_ids,
            'extra_products_product_ids' => $extra_products_ids,
            'related_product_ids' => $related_ids,
            'gallery_ids' => $gallery_ids,
            'gallery_ids_translated' => $gallery_ids_translated,
            'language_code' => $this->iniLocale,
            'slug' => $this->iniSlug,
            'name' => $this->iniName,
            'sub_name' => $this->iniSubName,
            'short_description' => $this->iniShortDesc,
            'description' => $this->iniDescription,
            'meta_title' => $this->iniMetaTitle,
            'meta_keywords' => $this->iniMetaKeywords,
            'meta_description' => $this->iniMetaDescription,
            'snippet_id' => $this->iniSnippetId,
            'a_plus_content_id' => $this->iniAPlusContent,
            'sec_a_plus_content_id' => $this->iniSecondAPlusContent,
            'reel_ids' => $reel_ids,
            'reel_ids_translated' => $reel_ids_translated,
            'shorts_urls' => $this->iniShortsUrls,
            'shorts_urls_translated' => $this->iniShortsUrlsTranslated,
        ];

        $rules = [
            'media_id' => 'nullable|integer|exists:media,id',
            'media_id_translated' => 'nullable|integer|exists:media,id',
            'calculator_id' => 'nullable|integer|exists:calculators,id',
            'a_plus_content_id' => 'nullable|integer|exists:page_translations,id',
            'sec_a_plus_content_id' => 'nullable|integer|exists:page_translations,id',
            'snippet_id' => 'nullable|integer|exists:page_translations,id',
            'priority' => 'required|integer',
            'related_reviewer_id' => 'nullable|integer|exists:products,id',
            'parent_skus' => ['required', 'string', new ValidParentSkusFormat(), new ValidParentSkusExist()],
            'type' => 'required|integer',
            'stock_status' => 'required|boolean',
            'regular_price' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
            'base_price' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
            'sales_price' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'tax_status' => 'required|boolean',
            'extra_product' => 'required|boolean',
            'enable_reviews' => 'required|boolean',
            'new' => 'required|boolean',
            'bestseller' => 'required|boolean',
            'status' => 'required|boolean',
            'translation_status' => 'required|boolean',
            'category_ids' => ['required', 'array'],
            'category_ids.*' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')
            ],
            'upsells_product_ids' => ['nullable', 'array'],
            'upsells_product_ids.*' => [
                'required',
                'integer',
                Rule::exists('products', 'id')
            ],
            'cross_sells_product_ids' => ['nullable', 'array'],
            'cross_sells_product_ids.*' => [
                'required',
                'integer',
                Rule::exists('products', 'id')
            ],
            'extra_products_product_ids' => ['nullable', 'array'],
            'extra_products_product_ids.*' => [
                'required',
                'integer',
                Rule::exists('products', 'id')
            ],
            'related_product_ids' => ['nullable', 'array'],
            'related_product_ids.*' => [
                'required',
                'integer',
                Rule::exists('products', 'id')
            ],
            'gallery_ids' => ['nullable', 'array'],
            'gallery_ids.*' => [
                'required',
                'integer',
                Rule::exists('media', 'id')
            ],
            'gallery_ids_translated' => ['nullable', 'array'],
            'gallery_ids_translated.*' => [
                'required',
                'integer',
                Rule::exists('media', 'id')
            ],
            'language_code' => 'required|exists:languages,code',
            'slug' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'sub_name' => 'nullable|string|max:1000',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'reel_ids' => ['nullable', 'array'],
            'reel_ids.*' => [
                'required',
                'integer',
                Rule::exists('media', 'id')
            ],
            'reel_ids_translated' => ['nullable', 'array'],
            'reel_ids_translated.*' => [
                'required',
                'integer',
                Rule::exists('media', 'id')
            ],
            'shorts_urls' => 'nullable|string',
            'shorts_urls_translated' => 'nullable|string',
        ];

        if ($this->importType == 2) {
            $rules['sku'] = 'required|string|max:50|unique:product_variants,sku,' . $productVariant->id;
            $rules['gtin'] = 'nullable|string|max:50|unique:products,gtin,' . $productVariant->product_id;
        } else if ($this->importType == 1) {
            $rules['sku'] = 'required|string|max:50|unique:product_variants,sku';
            $rules['gtin'] = 'nullable|string|max:50|unique:products,gtin';
        } else if ($this->importType == 3) {
            if (empty($productVariant)) {
                $rules['sku'] = 'required|string|max:80|unique:product_variants,sku';
                $rules['gtin'] = 'nullable|string|max:80|unique:products,gtin';
            } else {
                $rules['sku'] = 'required|string|max:80|unique:product_variants,sku,' . $productVariant->id;
                $rules['gtin'] = 'nullable|string|max:80|unique:products,gtin,' . $productVariant->product_id;
            }
        }

        if ($this->importType == 2 || ($this->importType == 3 && !empty($productVariant))) {
            $rules = $this->removeUnnecessaryKeysFromValidationArray($rules);
        }

        if ($productType == ProductConstants::TYPES['variable']) {
            $rules['parent_skus'] = [];
        }

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

    private function prepareBaseMedia($baseMedia)
    {
        $baseMedia = trim($baseMedia);
        $vendorKey = DB::connection()->getName();

        if ($baseMedia && str_contains($baseMedia, 'http') && validate_url_for_download_image($baseMedia)) {
            $fileUrl = $baseMedia;
            $fileInfoArray = pathinfo(basename($fileUrl));

            $originalName = Str::slug($fileInfoArray['filename'], '-');
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
                    $originalPath = "/uploads/{$vendorKey}/images/{$year}/{$month}/";
                    $image = Image::make($file->getRealPath());

                    $width = $image->width();
                    $height = $image->height();
                    $mediaSetting = $this->mediaSettingRepository->fetch('id, name, width, height', [], [], [], [], []);
                    $path = "/{$year}/{$month}/" . $newOriginalName . '.webp';
                    $type = MediaConstants::TYPE_IMAGE;

                    foreach ($mediaSetting as $setting) {
                        $size = strtolower($setting->name);
                        $filePath = "/uploads/{$vendorKey}/{$type}/{$size}/{$year}/{$month}/";
                        $dimensions = [$setting->width, $setting->height];
                        $this->fileService->save($file, $dimensions, $newOriginalName, $filePath, $fileType);

                        if ($fileType != 'image/webp' && $size === 'maximum') {
                            $this->fileService->save($file, $dimensions, $newOriginalName, $originalPath, $fileType, $originalExtension);
                        }

                        if ($fileType === 'image/webp' && $size === 'maximum') {
                            $originalPath = "/uploads/{$vendorKey}/$type/$size/{$year}/{$month}/";
                        }
                    }
                } else {
                    $type = MediaConstants::TYPE_FILE;
                    if (str_starts_with($fileType, 'video/')) {
                        $type = MediaConstants::TYPE_VIDEO;
                    } elseif (str_starts_with($fileType, 'image/')) {
                        $type = MediaConstants::TYPE_IMAGE;
                    }

                    $originalPath = "/uploads/{$vendorKey}/$type/{$year}/{$month}/";
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

    private function removeUnnecessaryKeysFromValidationArray(array $rules): array
    {
        if (str_contains(strtoupper($this->fileHeaders['iniGtin']), 'SKIP')) {
            unset($rules['gtin']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniBaseImg']), 'SKIP')) {
            unset($rules['media_id']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniBaseImgTranslated']), 'SKIP')) {
            unset($rules['media_id_translated']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniRelatedReviewerId']), 'SKIP')) {
            unset($rules['related_reviewer_id']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniPriority']), 'SKIP')) {
            unset($rules['priority']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniHSCode']), 'SKIP')) {
            unset($rules['hs_code']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniCalculatorId']), 'SKIP')) {
            unset($rules['calculator_id']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniAPlusContent']), 'SKIP')) {
            unset($rules['a_plus_content_id']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniSecondAPlusContent']), 'SKIP')) {
            unset($rules['sec_a_plus_content_id']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniSnippetId']), 'SKIP')) {
            unset($rules['snippet_id']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniParents']), 'SKIP')) {
            unset($rules['parent_skus']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniProductType']), 'SKIP')) {
            unset($rules['type']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniStockStatus']), 'SKIP')) {
            unset($rules['stock_status']);
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
        if (str_contains(strtoupper($this->fileHeaders['iniIsExtra']), 'SKIP')) {
            unset($rules['extra_product']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniVariantsExportIntoFeed']), 'SKIP')) {
            unset($rules['variants_export_into_feed']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniReviewAccess']), 'SKIP')) {
            unset($rules['enable_reviews']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniIsNew']), 'SKIP')) {
            unset($rules['new']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniIsNew']), 'SKIP')) {
            unset($rules['new']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniIsBestseller']), 'SKIP')) {
            unset($rules['bestseller']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniStatus']), 'SKIP')) {
            unset($rules['status']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniTranslationStatus']), 'SKIP')) {
            unset($rules['translation_status']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniCategoryIds']), 'SKIP')) {
            unset($rules['category_ids']);
            unset($rules['category_ids.*']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniUpsellsIds']), 'SKIP')) {
            unset($rules['upsells_product_ids']);
            unset($rules['upsells_product_ids.*']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniCrossSellsIds']), 'SKIP')) {
            unset($rules['cross_sells_product_ids']);
            unset($rules['cross_sells_product_ids.*']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniExtraProductIds']), 'SKIP')) {
            unset($rules['extra_products_product_ids']);
            unset($rules['extra_products_product_ids.*']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniRelatedIds']), 'SKIP')) {
            unset($rules['related_product_ids']);
            unset($rules['related_product_ids.*']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniGalleryIds']), 'SKIP')) {
            unset($rules['gallery_ids']);
            unset($rules['gallery_ids.*']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniGalleryIdsTranslated']), 'SKIP')) {
            unset($rules['gallery_ids_translated']);
            unset($rules['gallery_ids_translated.*']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniSlug']), 'SKIP')) {
            unset($rules['slug']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniName']), 'SKIP')) {
            unset($rules['name']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniSubName']), 'SKIP')) {
            unset($rules['sub_name']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniShortDesc']), 'SKIP')) {
            unset($rules['short_description']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniDescription']), 'SKIP')) {
            unset($rules['description']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniMetaTitle']), 'SKIP')) {
            unset($rules['meta_title']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniMetaKeywords']), 'SKIP')) {
            unset($rules['meta_keywords']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniMetaDescription']), 'SKIP')) {
            unset($rules['meta_description']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniReelIds']), 'SKIP')) {
            unset($rules['reel_ids']);
            unset($rules['reel_ids.*']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniReelIdsTranslated']), 'SKIP')) {
            unset($rules['reel_ids_translated']);
            unset($rules['reel_ids_translated.*']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniShortsUrls']), 'SKIP')) {
            unset($rules['shorts_urls']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniShortsUrlsTranslated']), 'SKIP')) {
            unset($rules['shorts_urls_translated']);
        }

        return $rules;
    }

    private function removeUnnecessaryProductsTableArray(array $array): array
    {
        if (str_contains(strtoupper($this->fileHeaders['iniCalculatorId']), 'SKIP')) {
            unset($array['calculator_id']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniProductType']), 'SKIP')) {
            unset($array['type']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniReviewAccess']), 'SKIP')) {
            unset($array['enable_reviews']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniIsNew']), 'SKIP')) {
            unset($array['new']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniIsExtra']), 'SKIP')) {
            unset($array['extra_product']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniVariantsExportIntoFeed']), 'SKIP')) {
            unset($array['variants_export_into_feed']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniIsBestseller']), 'SKIP')) {
            unset($array['bestseller']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniRelatedReviewerId']), 'SKIP')) {
            unset($array['related_reviewer_id']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniPriority']), 'SKIP')) {
            unset($array['priority']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniHSCode']), 'SKIP')) {
            unset($array['hs_code']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniGtin']), 'SKIP')) {
            unset($array['gtin']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniWatermarkImg']), 'SKIP')) {
            unset($array['watermark_settings']);
        }

        return $array;
    }

    private function removeUnnecessaryProductVariantsTableArray(array $array): array
    {
        if (str_contains(strtoupper($this->fileHeaders['iniBaseImg']), 'SKIP')) {
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
        if (str_contains(strtoupper($this->fileHeaders['iniTaxStatus']), 'SKIP')) {
            unset($array['tax_status']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniStatus']), 'SKIP')) {
            unset($array['status']);
        }

        return $array;
    }

    private function removeUnnecessaryProductVariantTranslationsTableArray(array $array): array
    {
        if (str_contains(strtoupper($this->fileHeaders['iniBaseImgTranslated']), 'SKIP')) {
            unset($array['media_id']);
        }

        return $array;
    }

    private function removeUnnecessaryProductTranslationsArray(array $array): array
    {
        if (str_contains(strtoupper($this->fileHeaders['iniSlug']), 'SKIP')) {
            unset($array['slug']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniName']), 'SKIP')) {
            unset($array['name']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniSubName']), 'SKIP')) {
            unset($array['sub_name']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniTranslationStatus']), 'SKIP')) {
            unset($array['translation_status']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniShortDesc']), 'SKIP')) {
            unset($array['short_description']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniDescription']), 'SKIP')) {
            unset($array['description']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniMetaTitle']), 'SKIP')) {
            unset($array['meta_title']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniMetaKeywords']), 'SKIP')) {
            unset($array['meta_keywords']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniMetaDescription']), 'SKIP')) {
            unset($array['meta_description']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniAPlusContent']), 'SKIP')) {
            unset($array['a_plus_content_id']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniSecondAPlusContent']), 'SKIP')) {
            unset($array['sec_a_plus_content_id']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniSnippetId']), 'SKIP')) {
            unset($array['snippet_id']);
        }
        if (str_contains(strtoupper($this->fileHeaders['iniWatermarkImgTranslated']), 'SKIP')) {
            unset($array['watermark_settings']);
        }

        return $array;
    }

    private function prepareHeadersFile(array $headers): array
    {
        return [
            'iniSku' => $headers[0],
            'iniGtin' => $headers[1],
            'iniParents' => $headers[2],
            'iniWatermarkImg' => $headers[3],
            'iniBaseImg' => $headers[4],
            'iniProductType' => $headers[5],
            'iniStockStatus' => $headers[6],
            'iniBasePrice' => $headers[7],
            'iniRegularPrice' => $headers[8],
            'iniSalesPrice' => $headers[9],
            'iniTaxStatus' => $headers[10],
            'iniReviewAccess' => $headers[11],
            'iniIsExtra' => $headers[12],
            'iniVariantsExportIntoFeed' => $headers[13],
            'iniIsNew' => $headers[14],
            'iniIsBestseller' => $headers[15],
            'iniStatus' => $headers[16],
            'iniVariableAttrIds' => $headers[17],
            'iniSimpleAttrIds' => $headers[18],
            'iniCategoryIds' => $headers[19],
            'iniUpsellsIds' => $headers[20],
            'iniCrossSellsIds' => $headers[21],
            'iniRelatedIds' => $headers[22],
            'iniRelatedReviewerId' => $headers[23],
            'iniExtraProductIds' => $headers[24],
            'iniCalculatorId' => $headers[25],
            'iniGalleryIds' => $headers[26],
            'iniPriority' => $headers[27],
            'iniHSCode' => $headers[28],
            'iniReelIds' => $headers[29],
            'iniShortsUrls' => $headers[30],
            'iniLocale' => $headers[32],
            'iniSlug' => $headers[33],
            'iniName' => $headers[34],
            'iniSubName' => $headers[35],
            'iniShortDesc' => $headers[36],
            'iniDescription' => $headers[37],
            'iniWatermarkImgTranslated' => $headers[38],
            'iniBaseImgTranslated' => $headers[39],
            'iniGalleryIdsTranslated' => $headers[40],
            'iniMetaTitle' => $headers[41],
            'iniMetaKeywords' => $headers[42],
            'iniMetaDescription' => $headers[43],
            'iniSnippetId' => $headers[44],
            'iniAPlusContent' => $headers[45],
            'iniSecondAPlusContent' => $headers[46],
            'iniTranslationStatus' => $headers[47],
            'iniReelIdsTranslated' => $headers[48],
            'iniShortsUrlsTranslated' => $headers[49],
        ];
    }

    private function processReelIds(array $reelIds, array &$processedIds, array &$reelInserts): void
    {
        foreach ($reelIds as $reelId) {
            if (empty($reelId)) {
                continue;
            }

            $videoInfo = str_contains($reelId, '~') ? explode('~', $reelId) : false;
            $id = $this->prepareBaseMedia($videoInfo ? $videoInfo[0] : $reelId);

            if (empty($id)) {
                continue;
            }

            if (!$videoInfo) {
                $videoType = null;
                $videoUrl = null;
            } else {
                $videoUrl = $videoInfo[1];
                $videoType = (str_contains(strtolower($videoUrl), 'youtube.') || str_contains(strtolower($videoUrl), 'youtu.')) ? 2 : 1;
            }

            $processedIds[] = $id;
            $reelInserts[] = [
                'media_id' => $id,
                'video_type' => $videoType,
                'video_url' => $videoUrl,
            ];
        }
    }
}
