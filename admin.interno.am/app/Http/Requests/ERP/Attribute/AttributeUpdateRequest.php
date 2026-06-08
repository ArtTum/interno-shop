<?php

namespace App\Http\Requests\ERP\Attribute;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AttributeUpdateRequest extends FormRequest
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
            'attribute_type_id' => 'nullable|integer',
            'value' => 'nullable|string|max:100',
            'slug' => 'nullable|string|max:255',
            'old_slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'media_id' => 'nullable|integer',
            'media' => 'nullable',
            'color_code' => 'nullable|string',
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
