<?php

namespace App\Http\Requests\ERP\ReminderEmail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReminderEmailInsertRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language_id' => 'required|integer',
            'name' => 'required',
            'order_status' => 'nullable',
            'payment_method' => 'nullable',
            'shipping_method' => 'nullable',
            'time_unit' => 'required|string',
            'newsletter_related' => 'required|boolean',
            'count' => 'required|integer|min:1'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
