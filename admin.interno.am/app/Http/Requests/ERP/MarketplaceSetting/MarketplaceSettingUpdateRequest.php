<?php

namespace App\Http\Requests\ERP\MarketplaceSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class MarketplaceSettingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer',
            'region' => 'required|integer',
            'client_id' => 'required|string|max:255',
            'client_secret' => 'required|string|max:255',
            'is_sandbox' => 'nullable|boolean',
            'dev_id' => 'nullable|string|max:255',
            'access_token' => 'nullable|string',
            'access_token_expires_at' => 'nullable|date',
            'refresh_token' => 'nullable|string',
            'refresh_token_expires_at' => 'nullable|integer',
            'token_type' => 'nullable|string|max:255',
            'scope' => 'nullable|string',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
