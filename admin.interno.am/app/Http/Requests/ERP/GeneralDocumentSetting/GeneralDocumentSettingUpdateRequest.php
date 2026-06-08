<?php

namespace App\Http\Requests\ERP\GeneralDocumentSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GeneralDocumentSettingUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'id' => 'required',
            'translation_id' => 'nullable',
            'language_id' => 'required|integer',
            'media_id' => 'nullable|integer',
            'name' => 'nullable|string|max:80',
            'phone' => 'nullable|string|max:80',
            'email' => 'nullable|string|max:80',
            'address' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'seller_info' => 'nullable|string',
            'rules' => 'nullable|array',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
