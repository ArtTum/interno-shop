<?php

namespace App\Http\Requests\ERP\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserFetchRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'per_page' => 'integer|required',
            'blocked' => 'integer|nullable',
            'only_actives' => 'integer|required',
            'user_group' => 'integer|nullable',
            'language_id' => 'integer|nullable',
            'member_group_id' => 'integer|nullable',
            'segment_id' => 'integer|nullable',
            'customer_group' => 'integer|nullable',
            'page' => 'integer|required',
            'search' => 'string|nullable',
            'ordering_field' => 'string|nullable',
            'type' => 'string|nullable',
            'ordering_direction' => 'string|nullable|in:asc,desc',
            'source' => 'integer|nullable',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
