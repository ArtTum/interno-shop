<?php

namespace App\Http\Requests\ERP\Language;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LanguageOutlookRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'id' => 'required',
            'callback' => 'required|string|max:255',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
