<?php

namespace App\Http\Requests\ERP\Media;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class MediaUploadRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'file' => 'required|mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,mp4,avi,mov|max:512000',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
