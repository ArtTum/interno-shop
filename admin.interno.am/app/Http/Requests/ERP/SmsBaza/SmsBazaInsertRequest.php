<?php

namespace App\Http\Requests\ERP\SmsBaza;

use App\Repositories\Disease\DiseaseRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SmsBazaInsertRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'year' => 'nullable|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
            'call_date' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'other_phone' => 'nullable|string|max:255',
            'sms_bazacol' => 'nullable|string|max:45',
            'patient_full_name' => 'required|string|max:255',
            'disease' => 'nullable|string|max:500',
            'medical_and_doctor' => 'nullable|string|max:500',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
