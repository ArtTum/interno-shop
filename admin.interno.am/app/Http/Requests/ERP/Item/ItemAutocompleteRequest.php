<?php

namespace App\Http\Requests\ERP\Item;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ItemAutocompleteRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'field' => 'nullable|string',
            'term' => 'nullable|string',
            'oldItems' => 'nullable|array',
            'alreadySelectIds' => 'nullable|array',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'forOrder' => filter_var($this->forOrder, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
