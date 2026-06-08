<?php

namespace App\Http\Requests\ERP\Subscribe;

use App\Repositories\Subscribe\SubscribeRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SubscribeFetchRequest extends FormRequest
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
            'year' => 'nullable|integer',
            'month' => 'nullable|integer',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
