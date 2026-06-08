<?php

namespace App\Http\Requests\ERP\Order;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class OrderAddItemRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'order_id' => 'required|integer',
            'product_variant_id' => 'required|integer',
            'language_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'price' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'tax_rate' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
