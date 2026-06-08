<?php

namespace App\Http\Requests\ERP\Attribute;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AttributeInsertRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'attribute_type_id' => 'integer|required',
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
