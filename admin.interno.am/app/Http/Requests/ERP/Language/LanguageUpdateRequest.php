<?php

namespace App\Http\Requests\ERP\Language;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LanguageUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'id' => 'required',
            'code' => 'required|string|max:255|unique:languages,code,' .  $this->request->get('id'),
            'old_code' => 'required|string',
            'name' => 'required|string',
            'status' => 'required|boolean',
            'draft' => 'required|boolean',
            'currency_id' => 'nullable|integer',
            'base' => 'required|boolean',
            'hreflang' => 'required|string|max:10',
            'local_for_trustpilot' => 'required|string|max:10',
            'default_hreflang' => 'required|boolean',
            'is_rtl' => 'required|boolean',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
