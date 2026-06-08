<?php

namespace App\Http\Requests\ERP\Outgoing;

use App\Repositories\Outgoing\OutgoingRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class OutgoingFetchRequest extends FormRequest
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
            'year' => 'integer|nullable',
            'month' => 'integer|nullable',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
