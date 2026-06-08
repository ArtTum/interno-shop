<?php

namespace App\Http\Requests\ERP\AttributeType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AttributeTypeInsertRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'default_sort_order' => 'integer|required',
            'type' => 'integer|required',
            'logic' => 'integer|required',
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
