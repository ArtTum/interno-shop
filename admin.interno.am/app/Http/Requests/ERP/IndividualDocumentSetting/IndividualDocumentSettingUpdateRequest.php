<?php

namespace App\Http\Requests\ERP\IndividualDocumentSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class IndividualDocumentSettingUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'id' => 'required',
            'translation_id' => 'nullable|integer',
            'language_id' => 'required|integer',
            'document_title' => 'nullable|string|max:80',
            'text_below_title' => 'nullable|string|max:80',
            'name' => 'nullable',
            'statuses' => 'nullable',
            'display_invoice_date' => 'nullable',
            'display_email_address' => 'nullable',
            'display_phone_number' => 'nullable',
            'generate_on_new_order' => 'nullable',
            'generate_invoice_also_in_base_language' => 'nullable',
            'number_format_prefix' => 'nullable',
            'create_automatically_after_refunding' => 'nullable',
            'display_credit_note_date' => 'nullable',
            'use_positive_prices' => 'nullable',
            'show_original_invoice_number' => 'nullable',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
