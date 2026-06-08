<?php

namespace App\Http\Requests\ERP\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'id' => 'required',
            'user_group_id' => 'nullable|integer',
            'member_group_id' => 'integer|nullable',
            'blocked' => 'nullable',
            'check_client_certificate' => 'nullable',
            'language_id' => 'nullable|integer',
            'status' => 'nullable',
            'socials' => 'nullable|array',
            'newsletter_subscribed' => 'nullable',
            'type' => 'string',
            'name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|string|max:200|email|unique:users,email,' .  $this->request->get('id'),
            'gtin' => 'nullable|string|max:100|unique:users,gtin,' .  $this->request->get('id'),
            'password' => 'nullable|string|min:2|string|max:255|confirmed',
            'password_confirmation' => 'nullable|string|max:255',
            'billing_addresses' => 'nullable',
            'shipping_addresses' => 'nullable',
            'ip' => 'nullable|max:250',
            'ip_expires_at' => 'nullable',
            'time' => 'nullable',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
