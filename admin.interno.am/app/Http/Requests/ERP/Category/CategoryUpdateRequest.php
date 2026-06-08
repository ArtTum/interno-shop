<?php

namespace App\Http\Requests\ERP\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CategoryUpdateRequest extends FormRequest
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
            'name' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'parent_id' => 'integer|nullable',
            'calculator_id' => 'integer|nullable',
            'related_category_translation_id' => 'integer|nullable',
            'snippet_id' => 'integer|nullable',
            'a_plus_content_id' => 'integer|nullable',
            'products_showing_type' => 'integer|nullable',
            'desktop' => 'integer|nullable',
            'tablet' => 'integer|nullable',
            'mobile' => 'integer|nullable',
            'price_adjustment' => 'nullable|numeric',
            'price_adjustment_old' => 'nullable|numeric',
            'slug' => 'nullable|string|max:255',
            'old_slug' => 'nullable|string|max:255',
            'media_id' => 'nullable|integer',
            'media' => 'nullable',
            'hide_for_front' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
