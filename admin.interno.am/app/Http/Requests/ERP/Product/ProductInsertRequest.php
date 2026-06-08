<?php

namespace App\Http\Requests\ERP\Product;

use App\Rules\ValidParentSkusExist;
use App\Rules\ValidParentSkusFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductInsertRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'attribute_type_variables' => 'nullable|array',
            'category_ids' => 'required|array',
            'primary_category_id' => 'nullable|integer',
            'product_type' => 'required|integer',
            'calculator_id' => 'nullable|integer',
            'regular_price' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
            'gift_card_prices' => 'nullable',
            'sales_price' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'hs_code' => 'nullable|string|max:50',
            'tax_status' => 'required|boolean',
            'sku' => 'required|string|max:50|unique:product_variants,sku',
            'gtin' => 'nullable|string|max:50|unique:products,gtin',
            'related_reviewer_id' => 'nullable|integer',
            'parents' => ['required', 'string', new ValidParentSkusFormat(), new ValidParentSkusExist()],
            'stock_status' => 'required|boolean',
            'independent_stock_status' => 'required|boolean',
            'upsells_product_ids' => 'nullable|array',
            'bundle_showing_type' => 'nullable|integer',
            'bundling_product_ids' => 'nullable|array',
            'extra_products_product_ids' => 'nullable|array',
            'required_extras' => 'nullable|array',
            'show_prices_extra' => 'nullable|array',
            'cross_sells_product_ids' => 'nullable|array',
            'related_product_ids' => 'nullable|array',
            'enable_reviews' => 'required|boolean',
            'new' => 'required|boolean',
            'bestseller' => 'required|boolean',
            'b2b' => 'nullable|integer',
            'status' => 'required|boolean',
            'extra_product' => 'required|boolean',
            'overwrite_price' => 'required|boolean',
            'variants_export_into_feed' => 'required|boolean',
            'gallery' => 'nullable',
            'media_id' => 'nullable|integer',
            'multiselect' => 'nullable|array',
            "multiselect.options_limit" => 'nullable|integer',
            "multiselect.title" => 'nullable|string',
            "multiselect.description" => 'nullable|string',
            "multiselect.options" => 'nullable|array',
            "multiselect.options.*.parents" => ['required', 'string', new ValidParentSkusFormat(), new ValidParentSkusExist()],
            "multiselect.options.*.media_id" => 'required',
            "multiselect.options.*.deleted" => 'nullable|boolean',
            "multiselect.options.*.additional_price" => 'required',
            'reel' => 'nullable',
            'shorts' => 'nullable',
        ];

        if ($this->product_type === 1 || $this->product_type === 4 || $this->product_type === 3) {
            $rules['parents'] = [];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'multiselect.options.*.parents' => "Item SKU's",
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
