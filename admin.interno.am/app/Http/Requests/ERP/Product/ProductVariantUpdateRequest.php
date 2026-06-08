<?php

namespace App\Http\Requests\ERP\Product;

use App\Rules\ValidParentSkusExist;
use App\Rules\ValidParentSkusFormat;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductVariantUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->language_id > -1) {
            $rules = [
                'id' => 'required|integer',
                'language_id' => 'required|integer',
                'translation_id' => 'nullable|integer',
                'name' => 'nullable|string',
                'short_description' => 'nullable|string',
                'description' => 'nullable|string',
                'media_id_translation' => 'nullable|integer',
                'gallery_translation' => 'nullable',
                'removeGalleryTranslation' => 'nullable'
//                "product_variant_custom_field_translation.*.id" => 'nullable|integer',
//                "product_variant_custom_field_translation.*.key" => 'required_if:product_variant_custom_field_translation.*.deleted,false|max:255',
//                "product_variant_custom_field_translation.*.value" => 'required_if:product_variant_custom_field_translation.*.deleted,false|max:255',
//                "product_variant_custom_field_translation.*.deleted" => 'required|boolean',
//                "product_variant_custom_field_translation.*.changed" => 'required|boolean',
            ];
        } else {
            $rules = [
                'id' => 'required|integer',
                'language_id' => 'required|integer',
                'product_id' => 'required|integer',
                'media_id' => 'nullable|integer',
                'sku' => 'required|string|max:50|unique:product_variants,sku,' . $this->request->get('id'),
                'parents' => ['required', 'string', new ValidParentSkusFormat(), new ValidParentSkusExist()],
                'regular_price' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
                'sales_price' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
                'tax_status' => 'required|boolean',
                'stock_status' => 'required|boolean',
                'independent_stock_status' => 'required|boolean',
                'status' => 'required|boolean',
                'gallery' => 'nullable',
                'removeGallery' => 'nullable'
            ];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'product_variant_custom_field_translation.*.key' => "custom field's key",
            'product_variant_custom_field_translation.*.value' => "custom field's value"
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
