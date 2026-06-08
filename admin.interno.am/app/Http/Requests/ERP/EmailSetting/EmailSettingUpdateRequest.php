<?php

namespace App\Http\Requests\ERP\EmailSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class EmailSettingUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required',
            'translation_id' => 'nullable',
            'language_id' => 'required|integer',
            'subject' => 'nullable',
            'title' => 'nullable|string',
            'top_text' => 'nullable|string',
            'bottom_text' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'admin_receiver_email_address' => 'nullable',
            'attach_document' => 'nullable',
        ];
    }

    protected function prepareForValidation()
    {

    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
