<?php

namespace App\Http\Requests\ERP\LoyaltyProgram;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoyaltyProgramInsertRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'spent' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
            'cashback' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
            'discount' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
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
