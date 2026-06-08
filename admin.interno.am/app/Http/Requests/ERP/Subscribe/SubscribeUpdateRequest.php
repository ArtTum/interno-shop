<?php

namespace App\Http\Requests\ERP\Subscribe;

use App\Repositories\Subscribe\SubscribeRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SubscribeUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'id'          => 'required',
            'full_name'   => 'required|string|max:255',
            'phone'       => 'nullable|string|max:50',
            'year'        => 'required|integer',
            'month'       => 'required|integer',
            'description' => 'nullable|string',
            'doctor'      => 'nullable|string|max:255',
            'hospital'    => 'nullable|string|max:255',
            'status'      => 'nullable|integer',
            'color'       => 'nullable|string|max:20',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
