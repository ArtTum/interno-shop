<?php

namespace App\Http\Requests\ERP\CookieSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CookieSettingUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language_id' => 'required|integer',
            'rows' => 'nullable|array',
            "rows.*.cookie_setting_id" => 'required|integer',
            "rows.*.translation" => 'required|string',
            "rows.*.id" => 'nullable|integer',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
