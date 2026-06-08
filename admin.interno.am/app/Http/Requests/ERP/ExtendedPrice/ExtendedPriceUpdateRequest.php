<?php

namespace App\Http\Requests\ERP\ExtendedPrice;

use App\Repositories\Disease\DiseaseRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExtendedPriceUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'id' => 'required|integer',
            'disease' => 'required|string|max:255',
            'price' => 'nullable|string|max:500',
            'sale_price' => 'nullable|string|max:255',
            'clinic' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:500',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
