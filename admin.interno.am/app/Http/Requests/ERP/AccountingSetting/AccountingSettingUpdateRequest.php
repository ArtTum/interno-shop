<?php

namespace App\Http\Requests\ERP\AccountingSetting;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AccountingSettingUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'row' => 'nullable|array',
            "statuses" => 'required',
            "row.*.key" => 'required|string|max:255',
            "row.*.value" => 'required|string|max:255',
            "row.*.label" => 'nullable|string|max:255',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
