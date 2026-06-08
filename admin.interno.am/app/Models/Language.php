<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency_id', 'code', 'name', 'status', 'base', 'local_for_trustpilot', 'hreflang', 'default_hreflang', 'email', 'microsoft_access_token', 'microsoft_refresh_token',
        'microsoft_token_expires_at', 'oauth_state', 'is_rtl'
    ];

    protected $casts = [
        'status' => 'boolean',
        'base' => 'boolean',
        'default_hreflang' => 'boolean',
        'is_rtl' => 'boolean'
    ];

    public function product_translation(): HasOne
    {
        return $this->hasOne(ProductTranslation::class);
    }

    public function attribute_type_translation(): HasOne
    {
        return $this->hasOne(AttributeTypeTranslation::class);
    }

    public function global_document_setting_translation(): HasOne
    {
        return $this->hasOne(GlobalDocumentSettingTranslation::class);
    }

    public function document_setting_translation(): HasOne
    {
        return $this->hasOne(DocumentSettingTranslation::class);
    }

    public function variable_translation(): HasOne
    {
        return $this->hasOne(VariableTranslation::class);
    }

    public function post_category_translation(): HasOne
    {
        return $this->hasOne(PostCategoryTranslation::class);
    }

    public function attribute_translation(): HasOne
    {
        return $this->hasOne(AttributeTranslation::class);
    }

    public function menu_translation(): HasOne
    {
        return $this->hasOne(MenuTranslation::class);
    }

    public function category_translation(): HasOne
    {
        return $this->hasOne(CategoryTranslation::class);
    }

    public function page_translation(): HasOne
    {
        return $this->hasOne(PageTranslation::class);
    }

    public function calculator_translation(): HasOne
    {
        return $this->hasOne(CalculatorTranslation::class);
    }

    public function shipping_zone_method_translation(): HasOne
    {
        return $this->hasOne(ShippingZoneMethodTranslation::class);
    }

    public function general_setting_translation(): HasOne
    {
        return $this->hasOne(GeneralSettingTranslation::class);
    }

    public function email_setting_translation(): HasOne
    {
        return $this->hasOne(EmailSettingTranslation::class);
    }

    public function media_translation(): HasOne
    {
        return $this->hasOne(MediaTranslation::class);
    }

    public function zip_rule_translation(): HasOne
    {
        return $this->hasOne(ZipRuleTranslation::class);
    }

    public function reminder_email_translation(): HasOne
    {
        return $this->hasOne(ReminderEmailTranslation::class);
    }

    public function social_translation(): HasOne
    {
        return $this->hasOne(SocialTranslation::class);
    }

    public function payment_method_translation(): HasOne
    {
        return $this->hasOne(PaymentMethodTranslation::class);
    }

    public function shipping_country(): HasOne
    {
        return $this->hasOne(Country::class);
    }
}
