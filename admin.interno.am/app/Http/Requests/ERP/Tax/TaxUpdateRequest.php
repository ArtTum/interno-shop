<?php

namespace App\Http\Requests\ERP\Tax;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TaxUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'params' => 'required|array',
            "params.*.id" => 'nullable|integer',
            "params.*.country_id" => 'required|integer',
            "params.*.name" => 'required|string|max:50',
            "params.*.state_code" => 'nullable|string|max:5',
            "params.*.zip" => 'nullable|string|max:200',
            "params.*.city" => 'nullable|string|max:50',
            "params.*.rate" => 'required|numeric|regex:/^\d+(\.\d+)?$/',
            "params.*.shipping" => 'boolean',
            "params.*.tax_free" => 'boolean',
            "params.*.changed" => 'nullable|boolean',
            "params.*.deleted" => 'nullable|boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            'params.*.country_id' => 'country',
            'params.*.name' => 'name',
            'params.*.state_code' => 'state',
            'params.*.zip' => 'zip',
            'params.*.city' => 'city',
            'params.*.shipping' => 'shipping',
            'params.*.rate' => 'rate'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
