<?php

namespace App\Http\Requests\ERP\Lead;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LeadProjectUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer',
            'translation_id' => 'nullable|integer',
            'language_id' => 'required|integer',
            'product_price' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'service_prices' => 'nullable|array',
            'name' => 'nullable|string',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
