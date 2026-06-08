<?php

namespace App\Http\Requests\ERP\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CategoryInsertRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'parent_id' => 'nullable|integer',
            'calculator_id' => 'integer|nullable',
            'price_adjustment' => 'nullable|numeric',
            'desktop' => 'nullable|integer',
            'mobile' => 'nullable|integer',
            'tablet' => 'nullable|integer',
            'products_showing_type' => 'required|integer',
            'media_id' => 'nullable|integer',
            'media' => 'nullable',
            'hide_for_front' => 'boolean',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
