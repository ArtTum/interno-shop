<?php

namespace App\Http\Requests\ERP\Translation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TranslationFetchRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'language_id' => 'integer|required',
            'per_page' => 'integer|required',
            'page' => 'integer|required',
            'search' => 'string|nullable',
            'ordering_field' => 'string|nullable',
            'ordering_direction' => 'string|nullable|in:asc,desc',
            'is_invalid' => 'nullable',
            'for_front' => 'integer|required',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
