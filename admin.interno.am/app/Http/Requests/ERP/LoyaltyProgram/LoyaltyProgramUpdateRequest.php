<?php

namespace App\Http\Requests\ERP\LoyaltyProgram;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoyaltyProgramUpdateRequest extends FormRequest
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
            'spent' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'cashback' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'discount' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'media_id' => 'nullable|integer',
            'media' => 'nullable',
            'options' => 'nullable',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
