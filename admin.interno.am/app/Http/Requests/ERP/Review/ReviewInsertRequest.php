<?php

namespace App\Http\Requests\ERP\Review;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReviewInsertRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'user_id' => 'integer|nullable',
            'product_id' => 'integer|nullable',
            'rating' => 'numeric|required',
            'name' => 'required|string|max:60',
            'country_code' => 'required|string|max:10',
            'created_at' => 'nullable|string',
            'email' => 'required|string|max:80|email',
            'status' => 'required',
            'text' => 'required|string',
            'images' => 'nullable',
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|mimes:jpg,jpeg,png,webp,mp4,avi,mkv,flv,mov,hevc,quicktime|max:102400',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
