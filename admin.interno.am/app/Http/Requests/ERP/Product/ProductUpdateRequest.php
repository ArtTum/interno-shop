<?php

namespace App\Http\Requests\ERP\Product;

use App\Rules\ValidParentSkusExist;
use App\Rules\ValidParentSkusFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        if ($this->language_id > -1) {
            $rules = [
                'id' => 'required|integer',
                'language_id' => 'required|integer',
                'product_variant_id' => 'required|integer',
                'translation_id' => 'nullable|integer',
                'slug' => 'nullable|string|max:255',
                'old_slug' => 'nullable|string|max:255',
                'name' => 'required|string|max:255',
                'sub_name' => 'nullable|string|max:1000',
                'translation_status' => 'nullable|boolean',
                'short_description' => 'nullable|string',
                'category_inheritance_calculator' => 'nullable|boolean',
                'category_inheritance_a_plus' => 'nullable|boolean',
                'category_inheritance_snippet' => 'nullable|boolean',
                'description' => 'nullable|string',
                'snippet_id' => 'integer|nullable',
                'a_plus_content_id' => 'integer|nullable',
                'sec_a_plus_content_id' => 'integer|nullable',
                'meta_title' => 'nullable|string|max:255',
                'meta_keywords' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string',
                'bundle_label' => 'nullable|string',
                'attributes_description_popup' => 'nullable',
                'custom_fields' => 'nullable|array',
                'gallery_translation' => 'nullable',
                'media_id_translation' => 'nullable|integer',
                'removeGalleryTranslation' => 'nullable',
                "custom_fields.*.id" => 'nullable|integer',
                "custom_fields.*.deleted" => 'required|boolean',
                "custom_fields.*.changed" => 'required|boolean',
                "custom_fields.*.key" => 'required_if:product_variant_custom_field_translation.*.deleted,false|max:255',
                "custom_fields.*.value" => 'required_if:product_variant_custom_field_translation.*.deleted,false|max:255',
                'watermark_settings' => 'nullable',
                'reel_translation' => 'nullable',
                'removeReelTranslation' => 'nullable',
                'shorts_translation' => 'nullable',
            ];
        } else {
            $rules =  [
                'id' => 'required|integer',
                'language_id' => 'required|integer',
                'category_ids' => 'required|array',
                'primary_category_id' => 'nullable|integer',
                'product_type' => 'required|integer',
                'calculator_id' => 'nullable|integer',
                'product_variant_id' => 'required|integer',
                'gift_card_prices' => 'nullable',
                'regular_price' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
                'regular_price_old' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
                'sales_price' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
                'hs_code' => 'nullable|string|max:50',
                'tax_status' => 'required|boolean',
                'sku' => 'required|string|max:50|unique:product_variants,sku,' .  $this->request->get('product_variant_id'),
                'gtin' => 'nullable|string|max:50|unique:products,gtin,' .  $this->request->get('id'),
                'parents' => ['required', 'string', new ValidParentSkusFormat(), new ValidParentSkusExist()],
                'stock_status' => 'required|boolean',
                'independent_stock_status' => 'required|boolean',
                'attribute_type_variables' => 'nullable|array',
                'upsells_product_ids' => 'nullable|array',
                'bundling_product_ids' => 'nullable|array',
                'bundle_showing_type' => 'nullable|integer',
                'extra_products_product_ids' => 'nullable|array',
                'required_extras' => 'nullable|array',
                'show_prices_extra' => 'nullable|array',
                'cross_sells_product_ids' => 'nullable|array',
                'related_product_ids' => 'nullable|array',
                'enable_reviews' => 'required|boolean',
                'new' => 'required|boolean',
                'bestseller' => 'required|boolean',
                'related_reviewer_id' => 'nullable|integer',
                'status' => 'required|boolean',
                'extra_product' => 'required|boolean',
                'overwrite_price' => 'required|boolean',
                'variants_export_into_feed' => 'required|boolean',
                'gallery' => 'nullable',
                'media_id' => 'nullable|integer',
                'b2b' => 'nullable|integer',
                'removeGallery' => 'nullable',
                'watermark_settings' => 'nullable',
                'tiered_prices' => 'nullable|array',
                "tiered_prices.*.id" => 'nullable|integer',

                "tiered_prices.*.deleted" => 'nullable|boolean',
                "tiered_prices.*.min" => 'nullable|integer',
                "tiered_prices.*.price" => 'required|numeric|regex:/^\d+(\.\d+)?$/',
                'reel' => 'nullable',
                'removeReel' => 'nullable',
                'shorts' => 'nullable',
            ];
        }

        if ($this->product_type === 1 || $this->product_type === 4 || $this->product_type === 3) {
            $rules['parents'] = [];
        }

        return array_merge($rules,
            [
                'multiselect' => 'nullable|array',
                "multiselect.id" => 'nullable|integer',
                "multiselect.options_limit" => 'nullable|integer',
                "multiselect.title" => 'nullable|string',
                "multiselect.description" => 'nullable|string',
                "multiselect.options" => 'nullable|array',
                "multiselect.options.*.parents" => ['required', 'string', new ValidParentSkusFormat(), new ValidParentSkusExist()],
                "multiselect.options.*.media_id" => 'required',
                "multiselect.options.*.deleted" => 'nullable|boolean',
                "multiselect.options.*.additional_price" => 'required',
                "multiselect.options.*.id" => 'nullable|integer',
                "multiselect.options.*.title" => 'nullable|string',
            ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
