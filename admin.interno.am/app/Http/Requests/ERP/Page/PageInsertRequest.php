<?php

namespace App\Http\Requests\ERP\Page;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PageInsertRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'parent_id' => 'integer|nullable',
            'language_id' => 'integer|required',
            'a_plus_content_type' => 'integer|nullable',
            'post_category_translation_id' => 'integer|nullable',
            'name' => 'string|required|max:250',
            'media_id' => 'integer|nullable',
            'page_type' => 'integer|required',
            'subname' => 'string|nullable|max:250',
            'button_text' => 'string|nullable|max:80',
            'published_at' => 'date|nullable',
            'slug' => 'string|nullable|max:250',
            'meta_title' => 'string|nullable|max:250',
            'meta_keywords' => 'string|nullable|max:250',
            'meta_description' => 'string|nullable',
            'path' => 'string|nullable',
            'breadcrumb' => 'string|nullable',
            'sections' => 'nullable|array',
            'status' => 'boolean|required',
            'user_level_ids' => 'array|nullable',
            'is_home' => 'boolean|required',
            'priority' => 'integer|required',
            'no_index' => 'boolean|required',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
