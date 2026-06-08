<?php

namespace App\Http\Requests\ERP\GeneralSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GeneralSettingUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'id' => 'required',
            'language_id' => 'required|integer',
            'translation_id' => 'nullable',
            'general_setting_id' => 'nullable|integer',
            'key' => 'required|string|max:255',
            'value' => 'nullable|string|max:255',
            'lang_value' => 'nullable|string|max:255',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
