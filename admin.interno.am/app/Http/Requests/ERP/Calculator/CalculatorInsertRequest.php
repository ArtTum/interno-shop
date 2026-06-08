<?php

namespace App\Http\Requests\ERP\Calculator;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CalculatorInsertRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'language_id' => 'required|integer',
            'name' => 'string|required|max:50',
            'coefficient' => 'required|numeric',
            'unit' => 'string|in:kg,gal',
            'formula' => 'nullable|string',
            'short_description' => 'nullable|string',
            'fields' => 'nullable|array'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
