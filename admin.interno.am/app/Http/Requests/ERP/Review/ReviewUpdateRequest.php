<?php

namespace App\Http\Requests\ERP\Review;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReviewUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'id' => 'required',
            'user_id' => 'nullable',
            'product_id' => 'integer|nullable',
            'rating' => 'numeric|required',
            'name' => 'required|string|max:60',
            'country_code' => 'required|string|max:10',
            'email' => 'required|string|max:80|email',
            'text' => 'string|nullable',
            'created_at' => 'nullable|string',
            'status' => 'required',
            'images' => 'nullable',
            'removeImages' => 'nullable',
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
