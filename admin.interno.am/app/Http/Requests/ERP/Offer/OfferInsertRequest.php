<?php

namespace App\Http\Requests\ERP\Offer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class OfferInsertRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'title' => 'required|string|max:50',
            'language_id' => 'required|integer',
            'offered_user_id' => 'required|integer',
            'currency_id' => 'required|integer',
            'shipping_cost' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'expire_days' => 'required|integer',
            'cart_data' => 'nullable|array',
            'carrier' => 'nullable',
            "cart_data.*.product_variation_id" => 'required|integer',
            "cart_data.*.quantity" => 'required|integer',
            'cart_data.*.price' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
