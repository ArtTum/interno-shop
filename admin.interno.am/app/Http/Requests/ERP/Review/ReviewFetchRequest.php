<?php

namespace App\Http\Requests\ERP\Review;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReviewFetchRequest extends FormRequest
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
            'search' => 'string|nullable',
            'ordering_field' => 'string|nullable',
            'ordering_direction' => 'string|nullable|in:asc,desc',
            'status' => 'nullable',
            'product_id' => 'nullable|integer',
            'attachments' => 'nullable|integer',
            'is_uploaded' => 'nullable',
            'user_id' => 'nullable|integer'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
