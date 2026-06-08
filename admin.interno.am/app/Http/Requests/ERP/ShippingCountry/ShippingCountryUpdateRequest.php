<?php

namespace App\Http\Requests\ERP\ShippingCountry;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ShippingCountryUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'id' => 'required|integer',
            'price_adjustment' => 'nullable|numeric',
            'language_id' => 'nullable|integer',
            'delivery_days_from' => 'required|integer',
            'delivery_days_to' => 'required|integer',
            'state_required' => 'required|boolean',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
