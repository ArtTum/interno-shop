<?php

namespace App\Http\Requests\ERP\Clinic;

use App\Repositories\Clinic\ClinicRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClinicUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'id' => 'required|integer',
            'clinic' => 'required|string|max:255',
            'name' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'sale' => 'nullable|string|max:255',
            'other_sale' => 'nullable|string|max:500',
            'address' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
