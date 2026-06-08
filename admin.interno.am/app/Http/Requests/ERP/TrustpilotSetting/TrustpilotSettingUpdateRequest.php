<?php

namespace App\Http\Requests\ERP\TrustpilotSetting;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TrustpilotSettingUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "id" => 'nullable|integer',
            'client_id' => 'required|string',
            "secret" => 'required|string',
            "bs_id" => 'nullable|string',
            "excellent_text" => 'nullable|string',
            "excluded_skus" => 'nullable|string',
            "page_id" => 'required|integer',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
