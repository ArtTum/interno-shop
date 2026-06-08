<?php

namespace App\Http\Requests\ERP\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductFetchRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'per_page' => 'integer|required',
            'product_type' => 'required|integer',
            'category_id' => 'required|integer',
            'attribute_id' => 'required|integer',
            'trash' => 'required|integer',
            'language_id' => 'required|integer',
            'item_id' => 'required|integer',
            'page' => 'integer|required',
            'search' => 'string|nullable',
            'ordering_field' => 'string|nullable',
            'ordering_direction' => 'string|nullable|in:asc,desc',
            'status' => 'required|integer',
            'enable_reviews' => 'required|integer',
            'translation' => 'required|integer',
            'bestseller' => 'required|integer',
            'tax_status' => 'required|integer',
            'stock_status' => 'required|integer',
            'sku' => 'nullable|string',
            'regular_price_from' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'regular_price_to' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'source' => 'integer|nullable',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
