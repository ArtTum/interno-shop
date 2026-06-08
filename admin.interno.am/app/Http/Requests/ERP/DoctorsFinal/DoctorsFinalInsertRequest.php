<?php

namespace App\Http\Requests\ERP\DoctorsFinal;

use App\Repositories\Disease\DiseaseRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DoctorsFinalInsertRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'full_name' => 'required|string|max:255',
            'profession' => 'nullable|string|max:255',
            'degree' => 'nullable|string|max:255',
            'workplace' => 'nullable|string|max:255',
            'other_info' => 'nullable|string|max:500',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
