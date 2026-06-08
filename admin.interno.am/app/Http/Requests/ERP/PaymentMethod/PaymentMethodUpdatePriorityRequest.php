<?php

namespace App\Http\Requests\ERP\PaymentMethod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PaymentMethodUpdatePriorityRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'language_id' => 'required|integer',
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
