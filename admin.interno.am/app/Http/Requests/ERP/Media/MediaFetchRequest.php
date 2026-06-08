<?php

namespace App\Http\Requests\ERP\Media;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class MediaFetchRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'per_page' => 'integer|required',
            'page' => 'integer|required',
            'search' => 'nullable',
            'type' => 'string|nullable',
            'year' => 'integer|nullable',
            'month' => 'integer|nullable',
            'mediaTypes' => 'nullable',
            'language_id' => 'integer|nullable',
            'translation' => 'integer|nullable',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
