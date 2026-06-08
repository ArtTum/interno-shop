<?php

namespace App\Http\Requests\ERP\AttributeType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AttributeTypeUpdatePriorityRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'newOrder' => 'required|array',
            'oldData' => 'required|array',
            "oldData.*.id" => 'integer',
            "oldData.*.priority" => 'integer|required'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
