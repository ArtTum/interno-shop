<?php

namespace App\Http\Requests\ERP\Menu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class MenuInsertRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'type' => 'required|integer',
            'parent_id' => 'nullable|integer',
            'category_translation_id' => 'nullable|integer',
            'product_translation_id' => 'nullable|integer',
            'page_translation_id' => 'nullable|integer',
            'new_tab' => 'boolean|required',
            'status' => 'boolean|required',
            'is_private' => 'boolean|nullable',
            'language_id' => 'required|integer',
            'url' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'text_for_all' => 'nullable|string|max:255',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
