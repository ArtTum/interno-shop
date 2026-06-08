<?php

namespace App\Http\Requests\ERP\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VendorInsertRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'name' => 'required|string',
            'db_server_ip' => 'required|string',
            'domain' => 'required|string|max:100',
            'checkout_country_ids' => 'required|array',
            'b2b' => 'required|integer',
            'shipping_and_labels' => 'nullable|array',
            'marketplaces' => 'nullable|array',
            'loyalty_programs' => 'boolean|nullable',
            'accounting_features' => 'boolean|nullable',
            'leads' => 'boolean|nullable',
            'abandoned_cart_emails' => 'boolean|nullable',
            'newsletter_system' => 'boolean|nullable',
            'dgd' => 'boolean|nullable',
            'cookie_management' => 'boolean|nullable',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
