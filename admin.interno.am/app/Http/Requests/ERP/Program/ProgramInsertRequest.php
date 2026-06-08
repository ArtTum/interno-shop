<?php

namespace App\Http\Requests\ERP\Program;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProgramInsertRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'name' => 'required|string|max:80',
            'status' => 'required|boolean',
            'sale_status' => 'required|boolean',
            'sale_amount' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'sale_type' => 'nullable|integer',
            'click_status' => 'required|boolean',
            'click_amount' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
