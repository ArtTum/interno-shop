<?php

namespace App\Http\Requests\ERP\AttributeType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AttributeTypeUpdateRequest extends FormRequest
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
            'name' => 'nullable|string|max:100',
            'label' => 'string|max:250',
            'description' => 'nullable|string',
            'slug' => 'nullable|string|max:255',
            'old_slug' => 'nullable|string|max:255',
            'default_sort_order' => 'integer|nullable',
            'type' => 'integer|nullable',
            'logic' => 'integer|nullable',
            'is_filterable' => 'boolean|required',
            'is_conditional' => 'boolean|required',
            'bigger_values' => 'boolean|required',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
