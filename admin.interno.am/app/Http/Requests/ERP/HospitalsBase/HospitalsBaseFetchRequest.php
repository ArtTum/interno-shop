<?php

namespace App\Http\Requests\ERP\HospitalsBase;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class HospitalsBaseFetchRequest extends FormRequest
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
            'year' => 'nullable',
            'month' => 'nullable',
            'color' => 'nullable',
            'find_about_us' => 'nullable',
            'day_surgery_start' => 'nullable',
            'day_surgery_end' => 'nullable',
            'call_date' => 'nullable',
            'next_call_date' => 'nullable',
            'user' => 'nullable',
            'hospital' => 'nullable',
            'diseases' => 'nullable',
            'konsultacia' => 'nullable',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
