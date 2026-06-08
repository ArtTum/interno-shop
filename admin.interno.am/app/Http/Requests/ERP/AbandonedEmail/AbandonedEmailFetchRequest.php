<?php

namespace App\Http\Requests\ERP\AbandonedEmail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AbandonedEmailFetchRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'per_page' => 'integer|required',
            'page' => 'integer|required',
            'language_id' => 'integer|required',
            'search' => 'string|nullable',
            'ordering_field' => 'string|nullable',
            'ordering_direction' => 'string|nullable|in:asc,desc',
            'created_at_from' => 'string|nullable',
            'created_at_to' => 'string|nullable'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
