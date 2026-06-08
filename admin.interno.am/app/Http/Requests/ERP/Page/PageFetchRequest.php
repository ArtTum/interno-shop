<?php

namespace App\Http\Requests\ERP\Page;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PageFetchRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'per_page' => 'integer|required',
            'page_type' => 'integer|required',
            'page' => 'integer|required',
            'language_id' => 'integer|required',
            'parent_id' => 'integer|nullable',
            'translation' => 'integer|required',
            'trash' => 'required|integer',
            'a_plus_content_type' => 'nullable|integer',
            'search' => 'string|nullable',
            'ordering_field' => 'string|nullable',
            'ordering_direction' => 'string|nullable|in:asc,desc'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
