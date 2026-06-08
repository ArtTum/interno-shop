<?php

namespace App\Http\Requests\ERP\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductAutocompleteRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'language_id' => 'nullable|integer',
            'field' => 'nullable|string',
            'term' => 'nullable|string',
            'alreadySelectIds' => 'nullable|array',
            'order_id' => 'nullable|integer',
            'forOrder' => 'nullable|boolean',
            'forOffer' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'forOrder' => filter_var($this->forOrder, FILTER_VALIDATE_BOOLEAN),
            'forOffer' => filter_var($this->forOffer, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
