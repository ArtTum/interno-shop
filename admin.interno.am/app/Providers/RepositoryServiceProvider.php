<?php

namespace App\Providers;

use App\Models\AffiliateProgram;

use App\Models\AllCountry;
use App\Models\Article;
use App\Models\Attribute;
use App\Models\AttributeTranslation;
use App\Models\AttributeType;
use App\Models\AttributeTypeTranslation;
use App\Models\Author;
use App\Models\Calculator;
use App\Models\CalculatorTranslation;
use App\Models\Campaign;
use App\Models\CampaignEmail;
use App\Models\CampaignEmailSegment;
use App\Models\CampaignEmailUser;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Clinic;
use App\Models\Component;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\CouponAllowedEmail;
use App\Models\CouponCategory;
use App\Models\CouponProduct;
use App\Models\Currency;
use App\Models\CustomerGroup;
use App\Models\CustomerSegment;
use App\Models\CustomerSegmentUser;
use App\Models\DgdSetting;
use App\Models\Disease;
use App\Models\DoctorsFinal;
use App\Models\DocumentSetting;
use App\Models\DocumentSettingTranslation;
use App\Models\EmailSetting;
use App\Models\extendedPrice;
use App\Models\GeneralSetting;
use App\Models\GeneralSettingTranslation;
use App\Models\GlobalDocumentSetting;
use App\Models\GlobalDocumentSettingTranslation;
use App\Models\Hospital;
use App\Models\Language;
use App\Models\MarketplaceSetting;
use App\Models\MediaSetting;
use App\Models\MemberGroup;
use App\Models\MenuTranslation;
use App\Models\NewsletterBlacklist;
use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outgoing;

use App\Models\Page;
use App\Models\PageCustomerGroup;
use App\Models\PageSection;
use App\Models\PageSectionComponent;
use App\Models\PageSectionComponentItem;
use App\Models\PageTranslation;
use App\Models\PageUserLevel;
use App\Models\PasswordResetToken;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodAccount;
use App\Models\PaymentMethodCountry;
use App\Models\PaymentMethodCurrency;
use App\Models\PaymentMethodTranslation;
use App\Models\PostCategory;
use App\Models\PostCategoryTranslation;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Service;
use App\Models\SmsBaza;
use App\Models\SmsShablon;
use App\Models\Social;
use App\Models\SocialTranslation;
use App\Models\SpeditionSetting;
use App\Models\Subscribe;
use App\Models\Tax;
use App\Models\TaxOrderFile;
use App\Models\TaxOrderSetting;
use App\Models\TntConsignmentNoteNumber;
use App\Models\TrustpilotSetting;
use App\Models\Upload;
use App\Models\UploadLog;
use App\Models\User;
use App\Models\UserAffiliate;
use App\Models\UserBillingAddress;
use App\Models\UserGroup;
use App\Models\UserGroupIP;
use App\Models\UserGroupPermission;
use App\Models\UserLevel;
use App\Models\UserLevelHistory;
use App\Models\UserLevelOption;
use App\Models\UserLevelTranslation;
use App\Models\UserShippingAddress;
use App\Models\ValidVatNumber;
use App\Models\Variable;
use App\Models\VariableTranslation;
use App\Models\Vendor;
use App\Models\VendorCheckoutCountry;
use App\Models\VendorCountry;
use App\Models\VendorOption;
use App\Models\ZipRule;
use App\Models\ZipRuleTranslation;
use App\Repositories\AffiliateProgram\AffiliateProgramRepository;
use App\Repositories\AllCountry\AllCountryRepository;
use App\Repositories\Article\ArticleRepository;
use App\Repositories\Attribute\AttributeRepository;
use App\Repositories\AttributeTranslation\AttributeTranslationRepository;
use App\Repositories\AttributeType\AttributeTypeRepository;
use App\Repositories\AttributeTypeTranslation\AttributeTypeTranslationRepository;
use App\Repositories\Author\AuthorRepository;
use App\Repositories\Calculator\CalculatorRepository;
use App\Repositories\CalculatorTranslation\CalculatorTranslationRepository;
use App\Repositories\Campaign\CampaignRepository;
use App\Repositories\CampaignEmail\CampaignEmailRepository;
use App\Repositories\CampaignEmailSegment\CampaignEmailSegmentRepository;
use App\Repositories\CampaignEmailUser\CampaignEmailUserRepository;
use App\Repositories\Cart\CartRepository;
use App\Repositories\CartItem\CartItemRepository;
use App\Repositories\Category\CategoryRepository;
use App\Repositories\CategoryTranslation\CategoryTranslationRepository;
use App\Repositories\CeilingType\CeilingTypeRepository;
use App\Repositories\Certificate\CertificateRepository;
use App\Repositories\ChandelierType\ChandelierTypeRepository;
use App\Repositories\CheckOzon\CheckOzonRepository;
use App\Repositories\CheckWildberries\CheckWildberriesRepository;
use App\Repositories\Clinic\ClinicRepository;
use App\Repositories\ColorBase\ColorBaseRepository;
use App\Repositories\ColorPlafond\ColorPlafondRepository;
use App\Repositories\ColorSuspension\ColorSuspensionRepository;
use App\Repositories\ColourTemperature\ColourTemperatureRepository;
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
use App\Repositories\ExtendedPrice\ExtendedPriceRepository;
use App\Repositories\Faq\FaqRepository;
use App\Repositories\Feed\FeedRepository;
use App\Repositories\FeedType\FeedTypeRepository;
use App\Repositories\Form\FormRepository;
use App\Repositories\GeneralSetting\GeneralSettingRepository;
use App\Repositories\GeneralSettingTranslation\GeneralSettingTranslationRepository;
use App\Repositories\GlobalDocumentSetting\GlobalDocumentSettingRepository;
use App\Repositories\GlobalDocumentSettingTranslation\GlobalDocumentSettingTranslationRepository;
use App\Repositories\Group\GroupRepository;
use App\Repositories\HomePage\HomePageRepository;
use App\Repositories\Hospital\HospitalRepository;
use App\Repositories\InstallationLocation\InstallationLocationRepository;
use App\Repositories\InstallationMethod\InstallationMethodRepository;
use App\Repositories\Ip\IpRepository;
use App\Repositories\Item\ItemRepository;
use App\Repositories\Lamp\LampRepository;
use App\Repositories\Language\LanguageRepository;
use App\Repositories\LeftMenu\LeftMenuRepository;
use App\Repositories\MarketplaceSetting\MarketplaceSettingRepository;
use App\Repositories\MaterialBase\MaterialBaseRepository;
use App\Repositories\MaterialPlafond\MaterialPlafondRepository;
use App\Repositories\MaterialSubstrate\MaterialSubstrateRepository;
use App\Repositories\MaterialSuspension\MaterialSuspensionRepository;
use App\Repositories\MediaSetting\MediaSettingRepository;
use App\Repositories\MemberGroup\MemberGroupRepository;
use App\Repositories\MenuTranslation\MenuTranslationRepository;
use App\Repositories\MobileMenu\MobileMenuRepository;
use App\Repositories\MountingType\MountingTypeRepository;
use App\Repositories\NewsletterBlacklist\NewsletterBlacklistRepository;
use App\Repositories\Offer\OfferRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\OrderBillingAddress\OrderBillingAddressRepository;
use App\Repositories\OrderCustomerEmail\OrderCustomerEmailRepository;
use App\Repositories\OrderDocument\OrderDocumentRepository;
use App\Repositories\OrderEmployee\OrderEmployeeRepository;
use App\Repositories\OrderFeedback\OrderFeedbackRepository;
use App\Repositories\OrderItem\OrderItemRepository;
use App\Repositories\OrderItemParent\OrderItemParentRepository;
use App\Repositories\OrderNote\OrderNoteRepository;
use App\Repositories\OrderRefund\OrderRefundRepository;
use App\Repositories\OrderRefundHistory\OrderRefundHistoryRepository;
use App\Repositories\OrderRefundItem\OrderRefundItemRepository;
use App\Repositories\OrderReminderEmail\OrderReminderEmailRepository;
use App\Repositories\OrderSentGiftCard\OrderSentGiftCardRepository;
use App\Repositories\OrderShippingAddress\OrderShippingAddressRepository;
use App\Repositories\OrderSubDocument\OrderSubDocumentRepository;
use App\Repositories\Outgoing\OutgoingRepository;
use App\Repositories\OzonBrait\OzonBraitRepository;
use App\Repositories\OzonDemidenco\OzonDemidencoRepository;
use App\Repositories\OzonSvetlofon\OzonSvetlofonRepository;
use App\Repositories\Page\PageRepository;
use App\Repositories\PageCustomerGroup\PageCustomerGroupRepository;
use App\Repositories\PageSection\PageSectionRepository;
use App\Repositories\PageSectionComponent\PageSectionComponentRepository;
use App\Repositories\PageSectionComponentItem\PageSectionComponentItemRepository;
use App\Repositories\PageTranslation\PageTranslationRepository;
use App\Repositories\PageUserLevel\PageUserLevelRepository;
use App\Repositories\PasswordResetToken\PasswordResetTokenRepository;
use App\Repositories\PaymentMethod\PaymentMethodRepository;
use App\Repositories\PaymentMethodAccount\PaymentMethodAccountRepository;
use App\Repositories\PaymentMethodCountry\PaymentMethodCountryRepository;
use App\Repositories\PaymentMethodCurrency\PaymentMethodCurrencyRepository;
use App\Repositories\PaymentMethodTranslation\PaymentMethodTranslationRepository;
use App\Repositories\Phase\PhaseRepository;
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
use App\Repositories\ProductTranslation\ProductTranslationRepository;
use App\Repositories\ProductVariant\ProductVariantRepository;
use App\Repositories\ProductVariantAttribute\ProductVariantAttributeRepository;
use App\Repositories\ProductVariantCustomFieldTranslation\ProductVariantCustomFieldTranslationRepository;
use App\Repositories\ProductVariantGallery\ProductVariantGalleryRepository;
use App\Repositories\ProductVariantParent\ProductVariantParentRepository;
use App\Repositories\ProductVariantPrice\ProductVariantPriceRepository;
use App\Repositories\ProductVariantTranslation\ProductVariantTranslationRepository;
use App\Repositories\ProductVariantTranslationGallery\ProductVariantTranslationGalleryRepository;
use App\Repositories\Region\RegionRepository;
use App\Repositories\ReminderEmail\ReminderEmailRepository;
use App\Repositories\ReminderEmailTranslation\ReminderEmailTranslationRepository;
use App\Repositories\Service\ServiceRepository;
use App\Repositories\SharedCart\SharedCartRepository;
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
use App\Repositories\Slider\SliderRepository;
use App\Repositories\SmsBaza\SmsBazaRepository;
use App\Repositories\SmsShablon\SmsShablonRepository;
use App\Repositories\Social\SocialRepository;
use App\Repositories\SocialTranslation\SocialTranslationRepository;
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
use App\Repositories\UserLevelHistory\UserLevelHistoryRepository;
use App\Repositories\UserLevelOption\UserLevelOptionRepository;
use App\Repositories\UserLevelTranslation\UserLevelTranslationRepository;
use App\Repositories\UserShippingAddress\UserShippingAddressRepository;
use App\Repositories\ValidVatNumber\ValidVatNumberRepository;
use App\Repositories\Variable\VariableRepository;
use App\Repositories\VariableTranslation\VariableTranslationRepository;
use App\Repositories\Vendor\VendorRepository;
use App\Repositories\VendorCheckoutCountry\VendorCheckoutCountryRepository;
use App\Repositories\VendorCountry\VendorCountryRepository;
use App\Repositories\VendorOption\VendorOptionRepository;
use App\Repositories\Yml\YmlRepository;
use App\Repositories\ZipRule\ZipRuleRepository;
use App\Repositories\ZipRuleTranslation\ZipRuleTranslationRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * RegisterRequest services.
     */
    public function register(): void
    {
        //////////////////////////////////////////////////////////////////////
        $this->app->bind(CampaignEmailRepository::class, function ($app) {
            return new CampaignEmailRepository($app->make(CampaignEmail::class));
        });
        $this->app->bind(AffiliateProgramRepository::class, function ($app) {
            return new AffiliateProgramRepository($app->make(AffiliateProgram::class));
        });
        $this->app->bind(UserAffiliateRepository::class, function ($app) {
            return new UserAffiliateRepository($app->make(UserAffiliate::class));
        });
        $this->app->bind(MemberGroupRepository::class, function ($app) {
            return new MemberGroupRepository($app->make(MemberGroup::class));
        });
        $this->app->bind(CampaignEmailUserRepository::class, function ($app) {
            return new CampaignEmailUserRepository($app->make(CampaignEmailUser::class));
        });
        $this->app->bind(CampaignEmailSegmentRepository::class, function ($app) {
            return new CampaignEmailSegmentRepository($app->make(CampaignEmailSegment::class));
        });
        $this->app->bind(CampaignRepository::class, function ($app) {
            return new CampaignRepository($app->make(Campaign::class));
        });
        $this->app->bind(CustomerSegmentUserRepository::class, function ($app) {
            return new CustomerSegmentUserRepository($app->make(CustomerSegmentUser::class));
        });
        $this->app->bind(CustomerSegmentRepository::class, function ($app) {
            return new CustomerSegmentRepository($app->make(CustomerSegment::class));
        });
        $this->app->bind(PageCustomerGroupRepository::class, function ($app) {
            return new PageCustomerGroupRepository($app->make(PageCustomerGroup::class));
        });
        $this->app->bind(ValidVatNumberRepository::class, function ($app) {
            return new ValidVatNumberRepository($app->make(ValidVatNumber::class));
        });
        $this->app->bind(UserRepository::class, function ($app) {
            return new UserRepository($app->make(User::class));
        });
        $this->app->bind(UserGroupIPRepository::class, function ($app) {
            return new UserGroupIPRepository($app->make(UserGroupIP::class));
        });
        $this->app->bind(VendorOptionRepository::class, function ($app) {
            return new VendorOptionRepository($app->make(VendorOption::class));
        });
        $this->app->bind(LanguageRepository::class, function ($app) {
            return new LanguageRepository($app->make(Language::class));
        });
        $this->app->bind(PaymentMethodAccountRepository::class, function ($app) {
            return new PaymentMethodAccountRepository($app->make(PaymentMethodAccount::class));
        });
        $this->app->bind(CategoryRepository::class, function ($app) {
            return new CategoryRepository($app->make(Category::class));
        });
        $this->app->bind(CategoryTranslationRepository::class, function ($app) {
            return new CategoryTranslationRepository($app->make(CategoryTranslation::class));
        });
        $this->app->bind(MenuTranslationRepository::class, function ($app) {
            return new MenuTranslationRepository($app->make(MenuTranslation::class));
        });
        $this->app->bind(CurrencyRepository::class, function ($app) {
            return new CurrencyRepository($app->make(Currency::class));
        });
        $this->app->bind(TaxRepository::class, function ($app) {
            return new TaxRepository($app->make(Tax::class));
        });
        $this->app->bind(CountryRepository::class, function ($app) {
            return new CountryRepository($app->make(Country::class));
        });

        $this->app->bind(SocialRepository::class, function ($app) {
            return new SocialRepository($app->make(Social::class));
        });
        $this->app->bind(SocialTranslationRepository::class, function ($app) {
            return new SocialTranslationRepository($app->make(SocialTranslation::class));
        });

        $this->app->bind(AttributeTypeRepository::class, function ($app) {
            return new AttributeTypeRepository($app->make(AttributeType::class));
        });
        $this->app->bind(AttributeTypeTranslationRepository::class, function ($app) {
            return new AttributeTypeTranslationRepository($app->make(AttributeTypeTranslation::class));
        });
        $this->app->bind(UserGroupRepository::class, function ($app) {
            return new UserGroupRepository($app->make(UserGroup::class));
        });
        $this->app->bind(CustomerGroupRepository::class, function ($app) {
            return new CustomerGroupRepository($app->make(CustomerGroup::class));
        });
        $this->app->bind(UserBillingAddressRepository::class, function ($app) {
            return new UserBillingAddressRepository($app->make(UserBillingAddress::class));
        });
        $this->app->bind(UserShippingAddressRepository::class, function ($app) {
            return new UserShippingAddressRepository($app->make(UserShippingAddress::class));
        });
        $this->app->bind(AttributeRepository::class, function ($app) {
            return new AttributeRepository($app->make(Attribute::class));
        });
        $this->app->bind(AttributeTranslationRepository::class, function ($app) {
            return new AttributeTranslationRepository($app->make(AttributeTranslation::class));
        });

        $this->app->bind(MediaSettingRepository::class, function ($app) {
            return new MediaSettingRepository($app->make(MediaSetting::class));
        });

        $this->app->bind(ProductReviewRepository::class, function ($app) {
            return new ProductReviewRepository($app->make(ProductReview::class));
        });

        $this->app->bind(CouponRepository::class, function ($app) {
            return new CouponRepository($app->make(Coupon::class));
        });
        $this->app->bind(CouponCategoryRepository::class, function ($app) {
            return new CouponCategoryRepository($app->make(CouponCategory::class));
        });
        $this->app->bind(CouponAllowedEmailRepository::class, function ($app) {
            return new CouponAllowedEmailRepository($app->make(CouponAllowedEmail::class));
        });
        $this->app->bind(CouponProductRepository::class, function ($app) {
            return new CouponProductRepository($app->make(CouponProduct::class));
        });
        $this->app->bind(OrderRepository::class, function ($app) {
            return new OrderRepository($app->make(Order::class));
        });

        $this->app->bind(OrderItemRepository::class, function ($app) {
            return new OrderItemRepository($app->make(OrderItem::class));
        });

        $this->app->bind(UserGroupPermissionRepository::class, function ($app) {
            return new UserGroupPermissionRepository($app->make(UserGroupPermission::class));
        });
        $this->app->bind(VariableRepository::class, function ($app) {
            return new VariableRepository($app->make(Variable::class));
        });
        $this->app->bind(VariableTranslationRepository::class, function ($app) {
            return new VariableTranslationRepository($app->make(VariableTranslation::class));
        });
        $this->app->bind(GlobalDocumentSettingRepository::class, function ($app) {
            return new GlobalDocumentSettingRepository($app->make(GlobalDocumentSetting::class));
        });
        $this->app->bind(GlobalDocumentSettingTranslationRepository::class, function ($app) {
            return new GlobalDocumentSettingTranslationRepository($app->make(GlobalDocumentSettingTranslation::class));
        });
        $this->app->bind(DocumentSettingRepository::class, function ($app) {
            return new DocumentSettingRepository($app->make(DocumentSetting::class));
        });
        $this->app->bind(DocumentSettingTranslationRepository::class, function ($app) {
            return new DocumentSettingTranslationRepository($app->make(DocumentSettingTranslation::class));
        });
        $this->app->bind(GeneralSettingRepository::class, function ($app) {
            return new GeneralSettingRepository($app->make(GeneralSetting::class));
        });
        $this->app->bind(GeneralSettingTranslationRepository::class, function ($app) {
            return new GeneralSettingTranslationRepository($app->make(GeneralSettingTranslation::class));
        });
        $this->app->bind(UploadRepository::class, function ($app) {
            return new UploadRepository($app->make(Upload::class));
        });
        $this->app->bind(UploadLogRepository::class, function ($app) {
            return new UploadLogRepository($app->make(UploadLog::class));
        });
        $this->app->bind(VendorRepository::class, function ($app) {
            return new VendorRepository($app->make(Vendor::class));
        });
        $this->app->bind(AllCountryRepository::class, function ($app) {
            return new AllCountryRepository($app->make(AllCountry::class));
        });
        $this->app->bind(VendorCountryRepository::class, function ($app) {
            return new VendorCountryRepository($app->make(VendorCountry::class));
        });
        $this->app->bind(VendorCheckoutCountryRepository::class, function ($app) {
            return new VendorCheckoutCountryRepository($app->make(VendorCheckoutCountry::class));
        });
        $this->app->bind(PageRepository::class, function ($app) {
            return new PageRepository($app->make(Page::class));
        });
        $this->app->bind(PageSectionRepository::class, function ($app) {
            return new PageSectionRepository($app->make(PageSection::class));
        });
        $this->app->bind(PageSectionComponentRepository::class, function ($app) {
            return new PageSectionComponentRepository($app->make(PageSectionComponent::class));
        });
        $this->app->bind(PageTranslationRepository::class, function ($app) {
            return new PageTranslationRepository($app->make(PageTranslation::class));
        });
        $this->app->bind(ComponentRepository::class, function ($app) {
            return new ComponentRepository($app->make(Component::class));
        });
        $this->app->bind(PageSectionComponentItemRepository::class, function ($app) {
            return new PageSectionComponentItemRepository($app->make(PageSectionComponentItem::class));
        });
        $this->app->bind(PostCategoryRepository::class, function ($app) {
            return new PostCategoryRepository($app->make(PostCategory::class));
        });
        $this->app->bind(PostCategoryTranslationRepository::class, function ($app) {
            return new PostCategoryTranslationRepository($app->make(PostCategoryTranslation::class));
        });

        $this->app->bind(CalculatorRepository::class, function ($app) {
            return new CalculatorRepository($app->make(Calculator::class));
        });
        $this->app->bind(CalculatorTranslationRepository::class, function ($app) {
            return new CalculatorTranslationRepository($app->make(CalculatorTranslation::class));
        });
        $this->app->bind(TaxOrderSettingRepository::class, function ($app) {
            return new TaxOrderSettingRepository($app->make(TaxOrderSetting::class));
        });
        $this->app->bind(TaxOrderFileRepository::class, function ($app) {
            return new TaxOrderFileRepository($app->make(TaxOrderFile::class));
        });
        $this->app->bind(CartRepository::class, function ($app) {
            return new CartRepository($app->make(Cart::class));
        });
        $this->app->bind(CartItemRepository::class, function ($app) {
            return new CartItemRepository($app->make(CartItem::class));
        });

        $this->app->bind(DgdSettingRepository::class, function ($app) {
            return new DgdSettingRepository($app->make(DgdSetting::class));
        });
        $this->app->bind(EmailSettingRepository::class, function ($app) {
            return new EmailSettingRepository($app->make(EmailSetting::class));
        });
        $this->app->bind(ZipRuleRepository::class, function ($app) {
            return new ZipRuleRepository($app->make(ZipRule::class));
        });
        $this->app->bind(ZipRuleTranslationRepository::class, function ($app) {
            return new ZipRuleTranslationRepository($app->make(ZipRuleTranslation::class));
        });

        $this->app->bind(PaymentMethodCountryRepository::class, function ($app) {
            return new PaymentMethodCountryRepository($app->make(PaymentMethodCountry::class));
        });

        $this->app->bind(TrustpilotSettingRepository::class, function ($app) {
            return new TrustpilotSettingRepository($app->make(TrustpilotSetting::class));
        });


        $this->app->bind(PasswordResetTokenRepository::class, function ($app) {
            return new PasswordResetTokenRepository($app->make(PasswordResetToken::class));
        });

        $this->app->bind(PaymentMethodRepository::class, function ($app) {
            return new PaymentMethodRepository($app->make(PaymentMethod::class));
        });
        $this->app->bind(PaymentMethodTranslationRepository::class, function ($app) {
            return new PaymentMethodTranslationRepository($app->make(PaymentMethodTranslation::class));
        });
        $this->app->bind(PaymentMethodCurrencyRepository::class, function ($app) {
            return new PaymentMethodCurrencyRepository($app->make(PaymentMethodCurrency::class));
        });
        $this->app->bind(TntConsignmentNoteNumberRepository::class, function ($app) {
            return new TntConsignmentNoteNumberRepository($app->make(TntConsignmentNoteNumber::class));
        });
        $this->app->bind(OfferRepository::class, function ($app) {
            return new OfferRepository($app->make(Offer::class));
        });
        $this->app->bind(UserLevelRepository::class, function ($app) {
            return new UserLevelRepository($app->make(UserLevel::class));
        });
        $this->app->bind(UserLevelTranslationRepository::class, function ($app) {
            return new UserLevelTranslationRepository($app->make(UserLevelTranslation::class));
        });
        $this->app->bind(UserLevelOptionRepository::class, function ($app) {
            return new UserLevelOptionRepository($app->make(UserLevelOption::class));
        });
        $this->app->bind(UserLevelHistoryRepository::class, function ($app) {
            return new UserLevelHistoryRepository($app->make(UserLevelHistory::class));
        });
        $this->app->bind(PageUserLevelRepository::class, function ($app) {
            return new PageUserLevelRepository($app->make(PageUserLevel::class));
        });
        /////
        $this->app->bind(HospitalRepository::class, function ($app) {
            return new HospitalRepository($app->make(Hospital::class));
        });
        $this->app->bind(DiseaseRepository::class, function ($app) {
            return new DiseaseRepository($app->make(Disease::class));
        });
        $this->app->bind(SmsShablonRepository::class, function ($app) {
            return new SmsShablonRepository($app->make(SmsShablon::class));
        });
        $this->app->bind(DoctorsFinalRepository::class, function ($app) {
            return new DoctorsFinalRepository($app->make(DoctorsFinal::class));
        });
        $this->app->bind(ExtendedPriceRepository::class, function ($app) {
            return new ExtendedPriceRepository($app->make(ExtendedPrice::class));
        });
        $this->app->bind(ClinicRepository::class, function ($app) {
            return new ClinicRepository($app->make(Clinic::class));
        });
        $this->app->bind(SmsBazaRepository::class, function ($app) {
            return new SmsBazaRepository($app->make(SmsBaza::class));
        });
        $this->app->bind(OutgoingRepository::class, function ($app) {
            return new OutgoingRepository($app->make(Outgoing::class));
        });
        $this->app->bind(SubscribeRepository::class, function ($app) {
            return new SubscribeRepository($app->make(Subscribe::class));
        });
        $this->app->bind(ServiceRepository::class, function ($app) {
            return new ServiceRepository($app->make(Service::class));
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
