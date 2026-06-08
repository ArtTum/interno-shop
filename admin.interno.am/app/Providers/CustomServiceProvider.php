<?php

namespace App\Providers;

use App\Client\AI\ChatGPT\ChatGPTClient;
use App\Client\Rate\CurrencyBeacon;
use App\Client\Rate\GbpRate;
use App\Client\ShippingLabel\DHLClient;
use App\Client\ShippingLabel\DPDClient;
use App\Client\ShippingLabel\FedexRestClient;
use App\Client\ShippingLabel\TNTClient;
use App\Client\Trustpilot\TrustpilotClient;
use App\Repositories\AbandonedEmail\AbandonedEmailRepository;
use App\Repositories\AffiliateProgram\AffiliateProgramRepository;
use App\Repositories\AllCountry\AllCountryRepository;
use App\Repositories\Attribute\AttributeRepository;
use App\Repositories\AttributeTranslation\AttributeTranslationRepository;
use App\Repositories\AttributeType\AttributeTypeRepository;
use App\Repositories\AttributeTypeTranslation\AttributeTypeTranslationRepository;
use App\Repositories\Calculator\CalculatorRepository;
use App\Repositories\CalculatorTranslation\CalculatorTranslationRepository;
use App\Repositories\Campaign\CampaignRepository;
use App\Repositories\CampaignEmail\CampaignEmailRepository;
use App\Repositories\CampaignEmailSegment\CampaignEmailSegmentRepository;
use App\Repositories\CampaignEmailUser\CampaignEmailUserRepository;
use App\Repositories\Category\CategoryRepository;
use App\Repositories\CategoryTranslation\CategoryTranslationRepository;
use App\Repositories\Clinic\ClinicRepository;
use App\Repositories\Component\ComponentRepository;
use App\Repositories\Country\CountryRepository;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\CouponAllowedEmail\CouponAllowedEmailRepository;
use App\Repositories\CouponCategory\CouponCategoryRepository;
use App\Repositories\CouponProduct\CouponProductRepository;
use App\Repositories\Currency\CurrencyRepository;
use App\Repositories\CustomerGroup\CustomerGroupRepository;
use App\Repositories\CustomerSegment\CustomerSegmentRepository;
use App\Repositories\CustomerSegmentUser\CustomerSegmentUserRepository;
use App\Repositories\DgdSetting\DgdSettingRepository;
use App\Repositories\Disease\DiseaseRepository;
use App\Repositories\DoctorsFinal\DoctorsFinalRepository;
use App\Repositories\DocumentSetting\DocumentSettingRepository;
use App\Repositories\DocumentSettingTranslation\DocumentSettingTranslationRepository;
use App\Repositories\EmailSetting\EmailSettingRepository;
use App\Repositories\EmailSettingTranslation\EmailSettingTranslationRepository;
use App\Repositories\ExtendedPrice\ExtendedPriceRepository;
use App\Repositories\Feed\FeedRepository;
use App\Repositories\FeedType\FeedTypeRepository;
use App\Repositories\GeneralSetting\GeneralSettingRepository;
use App\Repositories\GeneralSettingTranslation\GeneralSettingTranslationRepository;
use App\Repositories\GlobalDocumentSetting\GlobalDocumentSettingRepository;
use App\Repositories\GlobalDocumentSettingTranslation\GlobalDocumentSettingTranslationRepository;
use App\Repositories\Hospital\HospitalRepository;
use App\Repositories\Item\ItemRepository;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\MarketplaceSetting\MarketplaceSettingRepository;
use App\Repositories\Media\MediaRepository;
use App\Repositories\MediaSetting\MediaSettingRepository;
use App\Repositories\MediaTranslation\MediaTranslationRepository;
use App\Repositories\MemberGroup\MemberGroupRepository;
use App\Repositories\MenuTranslation\MenuTranslationRepository;
use App\Repositories\NewsletterBlacklist\NewsletterBlacklistRepository;
use App\Repositories\Offer\OfferRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\OrderBillingAddress\OrderBillingAddressRepository;
use App\Repositories\OrderCustomerEmail\OrderCustomerEmailRepository;
use App\Repositories\OrderDocument\OrderDocumentRepository;
use App\Repositories\OrderFeedback\OrderFeedbackRepository;
use App\Repositories\OrderInfo\OrderInfoRepository;
use App\Repositories\OrderItem\OrderItemRepository;
use App\Repositories\OrderItemParent\OrderItemParentRepository;
use App\Repositories\OrderNote\OrderNoteRepository;
use App\Repositories\OrderRefund\OrderRefundRepository;
use App\Repositories\OrderRefundHistory\OrderRefundHistoryRepository;
use App\Repositories\OrderRefundItem\OrderRefundItemRepository;
use App\Repositories\OrderReminderEmail\OrderReminderEmailRepository;
use App\Repositories\OrderShippingAddress\OrderShippingAddressRepository;
use App\Repositories\OrderSubDocument\OrderSubDocumentRepository;
use App\Repositories\Outgoing\OutgoingRepository;
use App\Repositories\Page\PageRepository;
use App\Repositories\PageCustomerGroup\PageCustomerGroupRepository;
use App\Repositories\PageSection\PageSectionRepository;
use App\Repositories\PageSectionComponent\PageSectionComponentRepository;
use App\Repositories\PageSectionComponentItem\PageSectionComponentItemRepository;
use App\Repositories\PageTranslation\PageTranslationRepository;
use App\Repositories\PageUserLevel\PageUserLevelRepository;
use App\Repositories\PaymentMethod\PaymentMethodRepository;
use App\Repositories\PaymentMethodAccount\PaymentMethodAccountRepository;
use App\Repositories\PaymentMethodCountry\PaymentMethodCountryRepository;
use App\Repositories\PaymentMethodCurrency\PaymentMethodCurrencyRepository;
use App\Repositories\PaymentMethodCustomerGroup\PaymentMethodCustomerGroupRepository;
use App\Repositories\PaymentMethodTranslation\PaymentMethodTranslationRepository;
use App\Repositories\PermalinkTranslation\PermalinkTranslationRepository;
use App\Repositories\PostCategory\PostCategoryRepository;
use App\Repositories\PostCategoryTranslation\PostCategoryTranslationRepository;
use App\Repositories\Product\ProductRepository;
use App\Repositories\ProductAffiliateProgram\ProductAffiliateProgramRepository;
use App\Repositories\ProductAttribute\ProductAttributeRepository;
use App\Repositories\ProductCategory\ProductCategoryRepository;
use App\Repositories\ProductGiftPrices\ProductGiftPricesRepository;
use App\Repositories\ProductMultiselect\ProductMultiselectRepository;
use App\Repositories\ProductMultiselectOption\ProductMultiselectOptionRepository;
use App\Repositories\ProductMultiselectOptionParent\ProductMultiselectOptionParentRepository;
use App\Repositories\ProductMultiselectOptionTranslation\ProductMultiselectOptionTranslationRepository;
use App\Repositories\ProductMultiselectTranslation\ProductMultiselectTranslationRepository;
use App\Repositories\ProductRelatedProduct\ProductRelatedProductRepository;
use App\Repositories\ProductReview\ProductReviewRepository;
use App\Repositories\ProductReviewAttachment\ProductReviewAttachmentRepository;
use App\Repositories\ProductReviewTranslation\ProductReviewTranslationRepository;
use App\Repositories\ProductTranslation\ProductTranslationRepository;
use App\Repositories\ProductVariant\ProductVariantRepository;
use App\Repositories\ProductVariantAttribute\ProductVariantAttributeRepository;
use App\Repositories\ProductVariantCustomFieldTranslation\ProductVariantCustomFieldTranslationRepository;
use App\Repositories\ProductVariantGallery\ProductVariantGalleryRepository;
use App\Repositories\ProductVariantParent\ProductVariantParentRepository;
use App\Repositories\ProductVariantPrice\ProductVariantPriceRepository;
use App\Repositories\ProductVariantReel\ProductVariantReelRepository;
use App\Repositories\ProductVariantShort\ProductVariantShortRepository;
use App\Repositories\ProductVariantTranslation\ProductVariantTranslationRepository;
use App\Repositories\ProductVariantTranslationGallery\ProductVariantTranslationGalleryRepository;
use App\Repositories\ProductVariantTranslationReel\ProductVariantTranslationReelRepository;
use App\Repositories\ProductVariantTranslationShort\ProductVariantTranslationShortRepository;
use App\Repositories\ReminderEmail\ReminderEmailRepository;
use App\Repositories\ReminderEmailTranslation\ReminderEmailTranslationRepository;
use App\Repositories\Service\ServiceRepository;
use App\Repositories\SharedCart\SharedCartRepository;
use App\Repositories\ShippingLabel\ShippingLabelRepository;
use App\Repositories\ShippingLabelSetting\ShippingLabelSettingRepository;
use App\Repositories\ShippingLabelSettingCollectionDetail\ShippingLabelSettingCollectionDetailRepository;
use App\Repositories\ShippingLabelSettingCountry\ShippingLabelSettingCountryRepository;
use App\Repositories\ShippingZone\ShippingZoneRepository;
use App\Repositories\ShippingZoneCountry\ShippingZoneCountryRepository;
use App\Repositories\ShippingZoneMethod\ShippingZoneMethodRepository;
use App\Repositories\ShippingZoneMethodCustomerGroup\ShippingZoneMethodCustomerGroupRepository;
use App\Repositories\ShippingZoneMethodFlatRate\ShippingZoneMethodFlatRateRepository;
use App\Repositories\ShippingZoneMethodFree\ShippingZoneMethodFreeRepository;
use App\Repositories\ShippingZoneMethodTranslation\ShippingZoneMethodTranslationRepository;
use App\Repositories\ShippingZoneMethodUserLevel\ShippingZoneMethodUserLevelRepository;
use App\Repositories\SmsBaza\SmsBazaRepository;
use App\Repositories\SmsShablon\SmsShablonRepository;
use App\Repositories\Social\SocialRepository;
use App\Repositories\SocialTranslation\SocialTranslationRepository;
use App\Repositories\SpeditionSetting\SpeditionSettingRepository;
use App\Repositories\Subscribe\SubscribeRepository;
use App\Repositories\Tax\TaxRepository;
use App\Repositories\TaxOrderFile\TaxOrderFileRepository;
use App\Repositories\TaxOrderSetting\TaxOrderSettingRepository;
use App\Repositories\TntConsignmentNoteNumber\TntConsignmentNoteNumberRepository;
use App\Repositories\TrustpilotSetting\TrustpilotSettingRepository;
use App\Repositories\Upload\UploadRepository;
use App\Repositories\UploadLog\UploadLogRepository;
use App\Repositories\User\UserRepository;
use App\Repositories\UserAffiliate\UserAffiliateRepository;
use App\Repositories\UserBillingAddress\UserBillingAddressRepository;
use App\Repositories\UserGroup\UserGroupRepository;
use App\Repositories\UserGroupIP\UserGroupIPRepository;
use App\Repositories\UserGroupPermission\UserGroupPermissionRepository;
use App\Repositories\UserLevel\UserLevelRepository;
use App\Repositories\UserLevelOption\UserLevelOptionRepository;
use App\Repositories\UserLevelTranslation\UserLevelTranslationRepository;
use App\Repositories\UserShippingAddress\UserShippingAddressRepository;
use App\Repositories\Variable\VariableRepository;
use App\Repositories\VariableTranslation\VariableTranslationRepository;
use App\Repositories\Vendor\VendorRepository;
use App\Repositories\VendorCheckoutCountry\VendorCheckoutCountryRepository;
use App\Repositories\VendorOption\VendorOptionRepository;
use App\Repositories\ZipRule\ZipRuleRepository;
use App\Repositories\ZipRuleTranslation\ZipRuleTranslationRepository;
use App\Services\ERP\Accounting\File\AccountingFileService;
use App\Services\ERP\Accounting\Setting\AccountingSettingService;
use App\Services\ERP\Affiliate\AffiliateProduct\AffiliateProductService;
use App\Services\ERP\Affiliate\MemberGroup\MemberGroupService;
use App\Services\ERP\Affiliate\Program\ProgramService;
use App\Services\ERP\Auth\AuthService;
use App\Services\ERP\B2B\CustomerGroup\CustomerGroupService;
use App\Services\ERP\Catalog\Attribute\AttributeService;
use App\Services\ERP\Catalog\AttributeType\AttributeTypeService;
use App\Services\ERP\Catalog\Category\CategoryService;
use App\Services\ERP\Clinic\ClinicService;
use App\Services\ERP\Dashboard\DashboardService;
use App\Services\ERP\Dashboard\Revenue\RevenueService;
use App\Services\ERP\Disease\DiseaseService;
use App\Services\ERP\DoctorsFinal\DoctorsFinalService;
use App\Services\ERP\ExtendedPrice\ExtendedPriceService;
use App\Services\ERP\Hospital\HospitalService;
use App\Services\ERP\Marketing\MyOffer\MyOfferService;
use App\Services\ERP\Marketing\LoyaltyProgram\LoyaltyProgramService;
use App\Services\ERP\Marketing\MySharedCart\MySharedCartService;
use App\Services\ERP\Marketing\Offer\OfferService;
use App\Services\ERP\Marketing\OfferStats\OfferStatsService;
use App\Services\ERP\Marketplaces\MarketplaceAuth\MarketplaceAuthService;
use App\Services\ERP\Marketplaces\MarketplaceSetting\MarketplaceSettingService;
use App\Services\ERP\Newsletter\BlacklistService;
use App\Services\ERP\Newsletter\Campaign\CampaignService;
use App\Services\ERP\Newsletter\EmailAds\EmailAdsService;
use App\Services\ERP\Outgoing\OutgoingService;
use App\Services\ERP\Recommendation\RecommendationService;
use App\Services\ERP\Report\Controlling\ControllingService;
use App\Services\ERP\Report\Customers\CustomersService;
use App\Services\ERP\Settings\ShippingCountries\ShippingCountryService;
use App\Services\ERP\Settings\SpeditionSetting\SpeditionSettingService;
use App\Services\ERP\Settings\TntConsignmentNoteNumber\TntConsignmentNoteNumberService;
use App\Services\ERP\SmsBaza\SmsBazaService;
use App\Services\ERP\SmsShablon\SmsShablonService;
use App\Services\ERP\Subscribe\SubscribeService;
use App\Services\ERP\Users\Segment\CustomerSegmentService;
use App\Services\Front\Category\CategoryService as CategoryServiceFront;
use App\Services\ERP\Catalog\Product\ProductService;
use App\Services\ERP\Content\Menu\MenuService;
use App\Services\ERP\Content\Page\PageService;
use App\Services\ERP\Content\PostCategory\PostCategoryService;
use App\Services\ERP\DocumentSetting\General\GeneralDocumentSettingService;
use App\Services\ERP\DocumentSetting\Individual\IndividualDocumentSettingService;
use App\Services\ERP\File\FileService;
use App\Services\ERP\General\GeneralInfoService;
use App\Services\ERP\Marketing\Coupon\CouponService;
use App\Services\ERP\Marketing\SharedCart\SharedCartService;
use App\Services\ERP\Marketing\SharedCartStats\SharedCartStatsService;
use App\Services\ERP\Media\MediaService;
use App\Services\ERP\Orders\OrderService;
use App\Services\ERP\Report\Analytics\AnalyticService;
use App\Services\ERP\Settings\Currency\CurrencyService;
use App\Services\ERP\Settings\Dgd\DgdSettingService;
use App\Services\ERP\Settings\EmailSetting\EmailSettingService;
use App\Services\ERP\Settings\General\GeneralService;
use App\Services\ERP\Settings\Language\LanguageService;
use App\Services\ERP\Settings\MediaSetting\MediaSettingService;
use App\Services\ERP\Settings\PaymentMethod\PaymentMethodService;
use App\Services\ERP\Settings\Permalink\PermalinkService;
use App\Services\ERP\Settings\ShippingLabel\ShippingLabelService;
use App\Services\ERP\Settings\ShippingZone\ShippingZoneService;
use App\Services\ERP\Settings\Social\SocialService;
use App\Services\ERP\Settings\Tax\TaxService;
use App\Services\ERP\Settings\Translation\TranslationService;
use App\Services\Translation\TranslationService as TranslatService;
use App\Services\ERP\Settings\TrustpilotSetting\TrustpilotSettingService;
use App\Services\ERP\Settings\ZipRule\ZipRuleService;
use App\Services\ERP\Tools\BankTransferProcessing\BankTransferProcessingService;
use App\Services\ERP\Tools\Calculator\CalculatorService;
use App\Services\ERP\Tools\Feed\FeedService;
use App\Services\ERP\Tools\ReminderEmail\ReminderEmailService;
use App\Services\ERP\Tools\Uploads\UploadService;
use App\Services\ERP\Users\Permission\PermissionService;
use App\Services\ERP\Users\Review\ReviewService;
use App\Services\ERP\Users\User\UserService;
use App\Services\ERP\Users\UserGroup\UserGroupService;
use App\Services\ERP\Vendor\Countries\AllCountryService;
use App\Services\ERP\Vendor\Vendors\VendorService;
use App\Services\ERP\Warehouse\Item\ItemService;
use Illuminate\Support\ServiceProvider;

class CustomServiceProvider extends ServiceProvider
{
    /**
     * RegisterRequest services.
     */
    public function register(): void
    {
        $this->app->bind(AuthService::class, function ($app) {
            return new AuthService($app->make(UserRepository::class));
        });
        $this->app->bind(TranslatService::class, function ($app) {
            return new TranslatService(
                $app->make(GeneralSettingRepository::class),
                $app->make(ChatGPTClient::class),
                $app->make(ProductReviewTranslationRepository::class),
                $app->make(LanguageRepository::class),
            );
        });
        $this->app->bind(CustomerSegmentService::class, function ($app) {
            return new CustomerSegmentService(
                $app->make(CustomerSegmentRepository::class),
                $app->make(CustomerSegmentUserRepository::class),
                $app->make(CountryRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(ProductRepository::class),
                $app->make(CategoryRepository::class),
                $app->make(UserRepository::class),
                $app->make(AbandonedEmailRepository::class),
            );
        });
        $this->app->bind(CampaignService::class, function ($app) {
            return new CampaignService(
                $app->make(CampaignRepository::class),
            );
        });
        $this->app->bind(EmailAdsService::class, function ($app) {
            return new EmailAdsService(
                $app->make(CampaignEmailRepository::class),
                $app->make(CustomerSegmentRepository::class),
                $app->make(CampaignEmailSegmentRepository::class),
                $app->make(CampaignRepository::class),
                $app->make(CustomerSegmentUserRepository::class),
                $app->make(CampaignEmailUserRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(UserGroupRepository::class),
                $app->make(UserRepository::class),
                $app->make(OrderRepository::class),
                $app->make(CurrencyRepository::class),
            );
        });
        $this->app->bind(GeneralInfoService::class, function ($app) {
            return new GeneralInfoService(
                $app->make(LanguageRepository::class),
                $app->make(VendorRepository::class),
                $app->make(UserRepository::class),
                $app->make(GeneralSettingRepository::class),
            );
        });
        $this->app->bind(BankTransferProcessingService::class, function ($app) {
            return new BankTransferProcessingService(
                $app->make(OrderRepository::class),
                $app->make(UploadRepository::class),
                $app->make(UploadLogRepository::class),
                $app->make(OrderNoteRepository::class)
            );
        });
        $this->app->bind(DashboardService::class, function ($app) {
            return new DashboardService(
                $app->make(OrderRepository::class),
                $app->make(CountryRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(OrderItemRepository::class),
                $app->make(ItemRepository::class),
                $app->make(PaymentMethodRepository::class),
            );
        });
        $this->app->bind(RevenueService::class, function ($app) {
            return new RevenueService(
                $app->make(OrderRepository::class),
                $app->make(CountryRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(OrderItemRepository::class),
                $app->make(ItemRepository::class),
                $app->make(PaymentMethodRepository::class),
            );
        });
        $this->app->bind(PaymentMethodService::class, function ($app) {
            return new PaymentMethodService(
                $app->make(PaymentMethodRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(PaymentMethodTranslationRepository::class),
                $app->make(CurrencyRepository::class),
                $app->make(CountryRepository::class),
                $app->make(PaymentMethodCurrencyRepository::class),
                $app->make(PaymentMethodCountryRepository::class),
                $app->make(PaymentMethodAccountRepository::class),
                $app->make(CustomerGroupRepository::class),
                $app->make(PaymentMethodCustomerGroupRepository::class),
            );
        });
        $this->app->bind(AnalyticService::class, function ($app) {
            return new AnalyticService(
                $app->make(CountryRepository::class),
                $app->make(OrderRepository::class),
                $app->make(CurrencyRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(AttributeTypeRepository::class),
                $app->make(ProductRepository::class),
                $app->make(OrderItemRepository::class),
                $app->make(CategoryRepository::class),
                $app->make(CategoryTranslationRepository::class),
                $app->make(CategoryServiceFront::class),
                $app->make(ItemRepository::class),
                $app->make(CouponRepository::class),
            );
        });
        $this->app->bind(CustomersService::class, function ($app) {
            return new CustomersService(
                $app->make(UserRepository::class),
                $app->make(CountryRepository::class),
                $app->make(OrderRepository::class),
                $app->make(CurrencyRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(CategoryRepository::class),
                $app->make(CategoryTranslationRepository::class),
                $app->make(CategoryServiceFront::class),
                $app->make(OrderItemRepository::class),
            );
        });
        $this->app->bind(ControllingService::class, function ($app) {
            return new ControllingService(
                $app->make(UserRepository::class),
                $app->make(CountryRepository::class),
                $app->make(OrderRepository::class),
                $app->make(CurrencyRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(CategoryRepository::class),
                $app->make(CategoryTranslationRepository::class),
                $app->make(CategoryServiceFront::class),
                $app->make(OrderItemRepository::class),
            );
        });
        $this->app->bind(UploadService::class, function ($app) {
            return new UploadService(
                $app->make(UploadRepository::class),
                $app->make(UploadLogRepository::class),
            );
        });
        $this->app->bind(LanguageService::class, function ($app) {
            return new LanguageService(
                $app->make(LanguageRepository::class),
                $app->make(CurrencyRepository::class),
            );
        });
        $this->app->bind(MySharedCartService::class, function ($app) {
            return new MySharedCartService(
                $app->make(SharedCartRepository::class),
                $app->make(UserGroupRepository::class),
                $app->make(UserRepository::class),
                $app->make(CurrencyRepository::class),
            );
        });
        $this->app->bind(MyOfferService::class, function ($app) {
            return new MyOfferService(
                $app->make(OfferRepository::class),
                $app->make(UserGroupRepository::class),
                $app->make(UserRepository::class),
                $app->make(CurrencyRepository::class),
            );
        });
        $this->app->bind(SharedCartService::class, function ($app) {
            return new SharedCartService(
                $app->make(SharedCartRepository::class),
                $app->make(UserGroupRepository::class),
                $app->make(UserRepository::class),
                $app->make(CurrencyRepository::class),
            );
        });
        $this->app->bind(SharedCartStatsService::class, function ($app) {
            return new SharedCartStatsService(
                $app->make(SharedCartRepository::class),
                $app->make(UserGroupRepository::class),
                $app->make(UserRepository::class),
                $app->make(CurrencyRepository::class),
            );
        });
        $this->app->bind(OfferStatsService::class, function ($app) {
            return new OfferStatsService(
                $app->make(OfferRepository::class),
                $app->make(UserGroupRepository::class),
                $app->make(UserRepository::class),
                $app->make(CurrencyRepository::class),
            );
        });
        $this->app->bind(ZipRuleService::class, function ($app) {
            return new ZipRuleService(
                $app->make(ZipRuleRepository::class),
                $app->make(ZipRuleTranslationRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(CountryRepository::class),
            );
        });
        $this->app->bind(CategoryService::class, function ($app) {
            return new CategoryService(
                $app->make(CategoryRepository::class),
                $app->make(CategoryTranslationRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(UploadRepository::class),
                $app->make(PageTranslationRepository::class),
                $app->make(CalculatorRepository::class),
                $app->make(CurrencyRepository::class),
                $app->make(FeedTypeRepository::class),
                $app->make(GeneralSettingRepository::class),
            );
        });
        $this->app->bind(AttributeService::class, function ($app) {
            return new AttributeService(
                $app->make(AttributeRepository::class),
                $app->make(AttributeTranslationRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(AttributeTypeRepository::class),
                $app->make(UploadRepository::class),
                $app->make(GeneralSettingRepository::class),
            );
        });
        $this->app->bind(CalculatorService::class, function ($app) {
            return new CalculatorService(
                $app->make(CalculatorRepository::class),
                $app->make(CalculatorTranslationRepository::class),
                $app->make(LanguageRepository::class)
            );
        });
        $this->app->bind(ProductService::class, function ($app) {
            return new ProductService(
                $app->make(ProductRepository::class),
                $app->make(ProductTranslationRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(AttributeTypeRepository::class),
                $app->make(CategoryRepository::class),
                $app->make(ProductVariantRepository::class),
                $app->make(ProductVariantCustomFieldTranslationRepository::class),
                $app->make(ProductVariantGalleryRepository::class),
                $app->make(ProductAttributeRepository::class),
                $app->make(ProductCategoryRepository::class),
                $app->make(ProductVariantParentRepository::class),
                $app->make(ProductRelatedProductRepository::class),
                $app->make(ProductVariantAttributeRepository::class),
                $app->make(UploadRepository::class),
                $app->make(PageTranslationRepository::class),
                $app->make(CalculatorRepository::class),
                $app->make(ItemRepository::class),
                $app->make(FeedTypeRepository::class),
                $app->make(CurrencyRepository::class),
                $app->make(ProductVariantTranslationRepository::class),
                $app->make(ProductMultiselectRepository::class),
                $app->make(ProductMultiselectTranslationRepository::class),
                $app->make(ProductMultiselectOptionRepository::class),
                $app->make(ProductMultiselectOptionTranslationRepository::class),
                $app->make(ProductMultiselectOptionParentRepository::class),
                $app->make(CategoryTranslationRepository::class),
                $app->make(CategoryServiceFront::class),
                $app->make(ProductVariantTranslationGalleryRepository::class),
                $app->make(ProductGiftPricesRepository::class),
                $app->make(ProductVariantPriceRepository::class),
                $app->make(CustomerGroupRepository::class),
                $app->make(ChatGPTClient::class),
                $app->make(GeneralSettingRepository::class),
                $app->make(ProductVariantReelRepository::class),
                $app->make(ProductVariantTranslationReelRepository::class),
                $app->make(ProductVariantShortRepository::class),
                $app->make(ProductVariantTranslationShortRepository::class),
            );
        });
        $this->app->bind(MenuService::class, function ($app) {
            return new MenuService(
                $app->make(MenuTranslationRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(CategoryRepository::class),
                $app->make(CategoryTranslationRepository::class),
                $app->make(ProductTranslationRepository::class),
                $app->make(UploadRepository::class),
                $app->make(PageTranslationRepository::class),
                $app->make(GeneralSettingRepository::class),
            );
        });
        $this->app->bind(PageService::class, function ($app) {
            return new PageService(
                $app->make(PageRepository::class),
                $app->make(PageTranslationRepository::class),
                $app->make(PageSectionRepository::class),
                $app->make(PageSectionComponentRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(ComponentRepository::class),
                $app->make(CategoryRepository::class),
                $app->make(CategoryTranslationRepository::class),
                $app->make(PageSectionComponentItemRepository::class),
                $app->make(PostCategoryTranslationRepository::class),
                $app->make(CalculatorTranslationRepository::class),
                $app->make(ProductTranslationRepository::class),
                $app->make(UploadRepository::class),
                $app->make(CustomerGroupRepository::class),
                $app->make(PageCustomerGroupRepository::class),
                $app->make(PageUserLevelRepository::class),
                $app->make(UserLevelRepository::class),
                $app->make(GeneralSettingRepository::class),
            );
        });
        $this->app->bind(CurrencyService::class, function ($app) {
            return new CurrencyService(
                $app->make(CurrencyRepository::class),
                $app->make(GbpRate::class)
            );
        });
        $this->app->bind(FileService::class, function () {
            return new FileService();
        });
        $this->app->bind(TaxService::class, function ($app) {
            return new TaxService($app->make(TaxRepository::class), $app->make(CountryRepository::class));
        });
        $this->app->bind(SocialService::class, function ($app) {
            return new SocialService(
                $app->make(SocialRepository::class),
                $app->make(SocialTranslationRepository::class),
                $app->make(LanguageRepository::class),
            );
        });
        $this->app->bind(ShippingZoneService::class, function ($app) {
            return new ShippingZoneService(
                $app->make(ShippingZoneRepository::class),
                $app->make(CountryRepository::class),
                $app->make(ShippingZoneCountryRepository::class),
                $app->make(ShippingZoneMethodRepository::class),
                $app->make(ShippingZoneMethodFreeRepository::class),
                $app->make(ShippingZoneMethodFlatRateRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(ShippingZoneMethodTranslationRepository::class),
                $app->make(ShippingZoneMethodCustomerGroupRepository::class),
                $app->make(CustomerGroupRepository::class),
                $app->make(UserLevelRepository::class),
                $app->make(ShippingZoneMethodUserLevelRepository::class),
            );
        });
        $this->app->bind(AttributeTypeService::class, function ($app) {
            return new AttributeTypeService(
                $app->make(AttributeTypeRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(AttributeTypeTranslationRepository::class),
                $app->make(UploadRepository::class),
                $app->make(GeneralSettingRepository::class)
            );
        });
        $this->app->bind(PostCategoryService::class, function ($app) {
            return new PostCategoryService(
                $app->make(PostCategoryRepository::class),
                $app->make(PostCategoryTranslationRepository::class),
                $app->make(LanguageRepository::class)
            );
        });
        $this->app->bind(UserGroupService::class, function ($app) {
            return new UserGroupService(
                $app->make(UserGroupRepository::class),
            );
        });
        $this->app->bind(MemberGroupService::class, function ($app) {
            return new MemberGroupService(
                $app->make(MemberGroupRepository::class),
            );
        });
        $this->app->bind(ProgramService::class, function ($app) {
            return new ProgramService(
                $app->make(AffiliateProgramRepository::class),
            );
        });
        $this->app->bind(AffiliateProductService::class, function ($app) {
            return new AffiliateProductService(
                $app->make(ProductAffiliateProgramRepository::class),
                $app->make(ProductRepository::class),
                $app->make(AffiliateProgramRepository::class),
                $app->make(LanguageRepository::class),
            );
        });
        $this->app->bind(CustomerGroupService::class, function ($app) {
            return new CustomerGroupService(
                $app->make(CustomerGroupRepository::class),
            );
        });
        $this->app->bind(PermissionService::class, function ($app) {
            return new PermissionService(
                $app->make(UserGroupPermissionRepository::class),
                $app->make(UserGroupRepository::class)
            );
        });
        $this->app->bind(UserService::class, function ($app) {
            return new UserService(
                $app->make(UserRepository::class),
                $app->make(UserGroupRepository::class),
                $app->make(CountryRepository::class),
                $app->make(UserBillingAddressRepository::class),
                $app->make(UserShippingAddressRepository::class),
                $app->make(UploadRepository::class),
                $app->make(CustomerGroupRepository::class),
                $app->make(CurrencyRepository::class),
                $app->make(OrderRepository::class),
                $app->make(OrderItemRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(MemberGroupRepository::class),
                $app->make(UserAffiliateRepository::class),
            );
        });
        $this->app->bind(ReviewService::class, function ($app) {
            return new ReviewService(
                $app->make(ProductReviewRepository::class),
                $app->make(UserRepository::class),
                $app->make(ProductReviewAttachmentRepository::class),
                $app->make(UploadRepository::class),
                $app->make(FileService::class),
                $app->make(MediaSettingRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(CountryRepository::class),
            );
        });
        $this->app->bind(MediaSettingService::class, function ($app) {
            return new MediaSettingService(
                $app->make(MediaSettingRepository::class),
            );
        });
        $this->app->bind(CouponService::class, function ($app) {
            return new CouponService(
                $app->make(CouponRepository::class),
                $app->make(CouponCategoryRepository::class),
                $app->make(CouponProductRepository::class),
                $app->make(CouponAllowedEmailRepository::class),
                $app->make(CategoryRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(CurrencyRepository::class),
                $app->make(UploadRepository::class),
            );
        });
        $this->app->bind(OrderService::class, function ($app) {
            return new OrderService(
                $app->make(OrderRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(OrderBillingAddressRepository::class),
                $app->make(OrderShippingAddressRepository::class),
                $app->make(OrderItemRepository::class),
                $app->make(ProductRepository::class),
                $app->make(ProductVariantRepository::class),
                $app->make(OrderNoteRepository::class),
                $app->make(OrderRefundRepository::class),
                $app->make(OrderRefundHistoryRepository::class),
                $app->make(OrderRefundItemRepository::class),
                $app->make(OrderDocumentRepository::class),
                $app->make(OrderSubDocumentRepository::class),
                $app->make(GlobalDocumentSettingRepository::class),
                $app->make(DocumentSettingRepository::class),
                $app->make(VariableRepository::class),
                $app->make(ShippingLabelRepository::class),
                $app->make(ShippingLabelSettingRepository::class),
                $app->make(DHLClient::class),
                $app->make(FedexRestClient::class),
                $app->make(DPDClient::class),
                $app->make(TNTClient::class),
                $app->make(ItemRepository::class),
                $app->make(TaxRepository::class),
                $app->make(CountryRepository::class),
                $app->make(GeneralSettingRepository::class),
                $app->make(CurrencyRepository::class),
                $app->make(OrderItemParentRepository::class),
                $app->make(OrderInfoRepository::class),
                $app->make(OrderFeedbackRepository::class),
                $app->make(DgdSettingRepository::class),
                $app->make(EmailSettingRepository::class),
                $app->make(SocialRepository::class),
                $app->make(TntConsignmentNoteNumberRepository::class),
                $app->make(CouponRepository::class),
                $app->make(OrderCustomerEmailRepository::class),
                $app->make(OfferRepository::class),
                $app->make(AttributeTypeRepository::class),
                $app->make(UserRepository::class),
            );
        });
        $this->app->bind(TranslationService::class, function ($app) {
            return new TranslationService(
                $app->make(VariableRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(VariableTranslationRepository::class),
                $app->make(UploadRepository::class),
                $app->make(GeneralSettingRepository::class),
            );
        });
        $this->app->bind(PermalinkService::class, function ($app) {
            return new PermalinkService(
                $app->make(PermalinkTranslationRepository::class),
                $app->make(LanguageRepository::class),
            );
        });
        $this->app->bind(GeneralDocumentSettingService::class, function ($app) {
            return new GeneralDocumentSettingService(
                $app->make(GlobalDocumentSettingRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(GlobalDocumentSettingTranslationRepository::class),
                $app->make(CountryRepository::class),
            );
        });
        $this->app->bind(ShippingCountryService::class, function ($app) {
            return new ShippingCountryService(
                $app->make(CountryRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(CountryRepository::class),
            );
        });
        $this->app->bind(SpeditionSettingService::class, function ($app) {
            return new SpeditionSettingService(
                $app->make(SpeditionSettingRepository::class),
                $app->make(CountryRepository::class),
                $app->make(LanguageRepository::class),
            );
        });
        $this->app->bind(IndividualDocumentSettingService::class, function ($app) {
            return new IndividualDocumentSettingService(
                $app->make(DocumentSettingRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(DocumentSettingTranslationRepository::class),
                $app->make(GlobalDocumentSettingRepository::class),
                $app->make(GlobalDocumentSettingTranslationRepository::class),
            );
        });
        $this->app->bind(GeneralService::class, function ($app) {
            return new GeneralService(
                $app->make(GeneralSettingRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(GeneralSettingTranslationRepository::class),
                $app->make(GeneralSettingRepository::class),
            );
        });
        $this->app->bind(AllCountryService::class, function ($app) {
            return new AllCountryService(
                $app->make(AllCountryRepository::class)
            );
        });
        $this->app->bind(VendorService::class, function ($app) {
            return new VendorService(
                $app->make(VendorRepository::class),
                $app->make(AllCountryRepository::class),
                $app->make(CountryRepository::class),
                $app->make(VendorCheckoutCountryRepository::class),
                $app->make(VendorOptionRepository::class),
            );
        });
        $this->app->bind(AccountingSettingService::class, function ($app) {
            return new AccountingSettingService($app->make(TaxOrderSettingRepository::class));
        });
        $this->app->bind(AccountingFileService::class, function ($app) {
            return new AccountingFileService(
                $app->make(TaxOrderFileRepository::class),
                $app->make(TaxOrderSettingRepository::class),
                $app->make(OrderRepository::class),
                $app->make(CurrencyRepository::class),
                $app->make(CurrencyBeacon::class),
                $app->make(OrderInfoRepository::class),
            );
        });
        $this->app->bind(ShippingLabelService::class, function ($app) {
            return new ShippingLabelService(
                $app->make(ShippingLabelSettingRepository::class),
                $app->make(ShippingLabelSettingCountryRepository::class),
                $app->make(ShippingLabelRepository::class),
                $app->make(CountryRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(ShippingLabelSettingCollectionDetailRepository::class),
            );
        });
        $this->app->bind(MarketplaceSettingService::class, function ($app) {
            return new MarketplaceSettingService(
                $app->make(MarketplaceSettingRepository::class),
            );
        });
        $this->app->bind(MediaService::class, function ($app) {
            return new MediaService(
                $app->make(MediaRepository::class),
                $app->make(MediaSettingRepository::class),
                $app->make(MediaTranslationRepository::class),
                $app->make(FileService::class),
                $app->make(LanguageRepository::class),
                $app->make(UploadRepository::class),
                $app->make(ProductRepository::class),
                $app->make(AttributeRepository::class),
                $app->make(GeneralSettingRepository::class)
            );
        });
        $this->app->bind(ItemService::class, function ($app) {
            return new ItemService(
                $app->make(ItemRepository::class),
                $app->make(UploadRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(ProductVariantRepository::class),
            );
        });
        $this->app->bind(DgdSettingService::class, function ($app) {
            return new DgdSettingService(
                $app->make(DgdSettingRepository::class),
            );
        });
        $this->app->bind(EmailSettingService::class, function ($app) {
            return new EmailSettingService(
                $app->make(EmailSettingRepository::class),
                $app->make(EmailSettingTranslationRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(GeneralSettingRepository::class)
            );
        });
        $this->app->bind(ReminderEmailService::class, function ($app) {
            return new ReminderEmailService(
                $app->make(ReminderEmailRepository::class),
                $app->make(ReminderEmailTranslationRepository::class),
                $app->make(TrustpilotSettingRepository::class),
                $app->make(LanguageRepository::class),
                $app->make(OrderRepository::class),
                $app->make(SocialRepository::class),
                $app->make(VariableRepository::class),
                $app->make(TrustpilotClient::class),
                $app->make(pageRepository::class),
                $app->make(OrderReminderEmailRepository::class),
                $app->make(PaymentMethodAccountRepository::class),
                $app->make(CountryRepository::class),
                $app->make(CurrencyRepository::class),
            );
        });
        $this->app->bind(FeedService::class, function ($app) {
            return new FeedService(
                $app->make(FeedTypeRepository::class),
                $app->make(FeedRepository::class),
                $app->make(LanguageRepository::class),
            );
        });
        $this->app->bind(TrustpilotSettingService::class, function ($app) {
            return new TrustpilotSettingService(
                $app->make(TrustpilotSettingRepository::class),
                $app->make(TrustpilotClient::class),
                $app->make(PageTranslationRepository::class),
                $app->make(LanguageRepository::class),
            );
        });
        $this->app->bind(TntConsignmentNoteNumberService::class, function ($app) {
            return new TntConsignmentNoteNumberService(
                $app->make(TntConsignmentNoteNumberRepository::class),
            );
        });
        $this->app->bind(OfferService::class, function ($app) {
            return new OfferService(
                $app->make(OfferRepository::class),
                $app->make(CurrencyRepository::class),
                $app->make(LanguageRepository::class),
            );
        });
        $this->app->bind(LoyaltyProgramService::class, function ($app) {
            return new LoyaltyProgramService(
                $app->make(UserLevelRepository::class),
                $app->make(UserLevelTranslationRepository::class),
                $app->make(UserLevelOptionRepository::class),
                $app->make(LanguageRepository::class),
            );
        });
        $this->app->bind(BlacklistService::class, function ($app) {
            return new BlacklistService(
                $app->make(NewsletterBlacklistRepository::class),
            );
        });
        $this->app->bind(MarketplaceAuthService::class, function ($app) {
            return new MarketplaceAuthService(
                $app->make(MarketplaceSettingRepository::class),
            );
        });

        ////
        $this->app->bind(HospitalService::class, function ($app) {
            return new HospitalService(
                $app->make(HospitalRepository::class),
            );
        });

        $this->app->bind(DiseaseService::class, function ($app) {
            return new DiseaseService(
                $app->make(DiseaseRepository::class),
            );
        });
        $this->app->bind(SmsShablonService::class, function ($app) {
            return new SmsShablonService(
                $app->make(SmsShablonRepository::class),
            );
        });
        $this->app->bind(DoctorsFinalService::class, function ($app) {
            return new DoctorsFinalService(
                $app->make(DoctorsFinalRepository::class),
            );
        });
        $this->app->bind(ExtendedPriceService::class, function ($app) {
            return new ExtendedPriceService(
                $app->make(ExtendedPriceRepository::class),
            );
        });
        $this->app->bind(ClinicService::class, function ($app) {
            return new ClinicService(
                $app->make(ClinicRepository::class),
            );
        });
        $this->app->bind(SmsBazaService::class, function ($app) {
            return new SmsBazaService(
                $app->make(SmsBazaRepository::class),
            );
        });
        $this->app->bind(OutgoingService::class, function ($app) {
            return new OutgoingService(
                $app->make(OutgoingRepository::class),
            );
        });
        $this->app->bind(SubscribeService::class, function ($app) {
            return new SubscribeService(
                $app->make(SubscribeRepository::class),
            );
        });
        $this->app->bind(RecommendationService::class, function ($app) {
            return new RecommendationService(
                $app->make(ServiceRepository::class),
                $app->make(HospitalRepository::class),
                $app->make(DiseaseRepository::class),
                $app->make(UserRepository::class),
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
