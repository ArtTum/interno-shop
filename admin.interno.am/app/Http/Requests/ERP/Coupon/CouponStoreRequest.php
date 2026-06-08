<?php

namespace App\Http\Requests\ERP\Coupon;

use App\Rules\ValidCouponAllowedEmailsFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CouponStoreRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:coupons,code',
            'type' => 'required|integer',
            'amount' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'free_shipping' => 'required|boolean',
            'status' => 'required|boolean',
            'description' => 'nullable|string',
            'expires_at' => 'nullable|string',
            'min_spend' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'max_spend' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'product_ids' => 'array|nullable',
            'excluded_product_ids' => 'array|nullable',
            'category_ids' => 'array|nullable',
            'excluded_category_ids' => 'array|nullable',
            'exclude_sale_items' => 'required|boolean',
            'allowed_emails' => ['nullable', 'string', new ValidCouponAllowedEmailsFormat()],
            'usage_limit' => 'nullable|integer',
            'usage_limit_per_user' => 'nullable|integer'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
