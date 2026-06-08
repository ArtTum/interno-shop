<?php

namespace App\Http\Requests\ERP\Product;

use App\Rules\ValidParentSkusExist;
use App\Rules\ValidParentSkusFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductVariantStoreRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|integer',
            'media_id' => 'nullable|integer',
            'sku' => 'required|string|unique:product_variants,sku',
            'parents' => ['required', 'string', new ValidParentSkusFormat(), new ValidParentSkusExist()],
            'regular_price' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
            'sales_price' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'tax_status' => 'required|boolean',
            'stock_status' => 'required|boolean',
            'independent_stock_status' => 'required|boolean',
            'status' => 'required|boolean',
            'gallery' => 'nullable',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
