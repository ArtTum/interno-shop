<?php

namespace App\Http\Requests\ERP\SharedCartStats;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class FetchRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'per_page' => 'integer|required',
            'user_group_id' => 'integer|required',
            'page' => 'integer|required',
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
