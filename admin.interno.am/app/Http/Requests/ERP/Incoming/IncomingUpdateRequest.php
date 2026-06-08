<?php

namespace App\Http\Requests\ERP\Incoming;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class IncomingUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'id' => 'required',
            'patient_full_name' => 'required|string|max:250',
            'age' => 'nullable',
            'phone' => 'required|string|max:50',
            'year' => 'required',
            'month' => 'required',
            'call_date' => 'required|date_format:Y-m-d',
            'next_call_date' => 'nullable|date_format:Y-m-d',
            'disease_id' => 'nullable',
            'hospital_id' => 'nullable',
            'disease' => 'nullable',
            'medical_and_doctor' => 'nullable',
            'other_phone' => 'nullable',
            'find_aboutus' => 'required',
            'info' => 'nullable',
            'additional_data' => 'nullable',
            'incoming_color' => 'nullable',
            'month_copy' => 'nullable',
            'day_surgery' => 'nullable|date_format:Y-m-d',
            'konsultacia' => 'required',
            'user_id' => 'required',
            'price' => 'nullable',
            'sale_price' => 'nullable',
            'sale' => 'nullable',
            'a_d' => 'nullable',
        ];
    }

    protected function prepareForValidation(): void
    {
        $dates = [];
        foreach (['call_date', 'next_call_date', 'day_surgery'] as $field) {
            $value = $this->input($field);
            if ($value !== null && $value !== '') {
                $dates[$field] = str_replace('.', '-', substr($value, 0, 10));
            } else {
                $dates[$field] = null;
            }
        }
        $this->merge($dates);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
