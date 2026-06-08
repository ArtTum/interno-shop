<?php

namespace App\Http\Requests\ERP\HospitalsBase;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class HospitalsBaseUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->call_date && preg_match('/^\d{2}\.\d{2}\.\d{4}$/', trim($this->call_date))) {
            $this->merge(['call_date' => \Carbon\Carbon::createFromFormat('d.m.Y', trim($this->call_date))->format('Y-m-d')]);
        }
    }

    public function rules() : array
    {
        return [
            'id' => 'required',
            'patient_full_name' => 'nullable|string|max:250',
            'age' => 'nullable',
            'phone' => 'required|string|max:50',
            'year' => 'required',
            'month' => 'required',
            'call_date' => 'required|date_format:Y-m-d',
            'disease' => 'nullable',
            'other_phone' => 'nullable',
            'find_aboutus' => 'nullable',
            'additional_data' => 'nullable',
            'color' => 'nullable',
            'konsultacia' => 'nullable',
            'user_id' => 'nullable',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
