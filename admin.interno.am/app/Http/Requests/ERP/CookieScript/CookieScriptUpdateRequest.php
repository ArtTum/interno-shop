<?php

namespace App\Http\Requests\ERP\CookieScript;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CookieScriptUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'integer|required',
            'translation_id' => 'nullable|integer',
            'key' => 'nullable|string',
            'language_id' => 'nullable|string',
            'code' => 'nullable|string',
            "granted_anyway" => 'nullable|boolean',
            "consent_mode_v2" => 'nullable|boolean',
            "required_cookie" => 'nullable|boolean',
            "name" => 'nullable|string',
            "description" => 'nullable|string',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
