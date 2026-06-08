<?php

namespace App\Http\Requests\ERP\PaymentMethod;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PaymentMethodAccountUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer',
            'payment_method_id' => 'required|integer',
            'is_base' => 'required|boolean',
            'info' => 'array',
            'info.account_name' => 'required',
            'info.account_number' => 'nullable',
            'info.bank_name' => 'required',
            'info.sort_code' => 'nullable',
            'info.iban' => 'required',
            'info.bic_swift' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
