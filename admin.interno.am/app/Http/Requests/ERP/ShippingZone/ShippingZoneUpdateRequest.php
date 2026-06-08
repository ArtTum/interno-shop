<?php

namespace App\Http\Requests\ERP\ShippingZone;

use App\Constants\ShippingConstants;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ShippingZoneUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer',
            'name' => 'string|required|max:50',
            'country_ids' => 'array|required',
            'language_id' => 'integer|required',
            'shippingMethods' => 'array|required',
            "shippingMethods.*.id" => 'nullable|integer',
            "shippingMethods.*.user_level_ids" => 'array|nullable',
            "shippingMethods.*.hide_user_level_ids" => 'array|nullable',
            'shippingMethods.*.shipping_method_translation_id' => 'nullable|integer',
            "shippingMethods.*.name" => 'string|required|max:50',
            "shippingMethods.*.description" => 'string|nullable',
            "shippingMethods.*.type" => 'string|required',
            "shippingMethods.*.carrier" => 'required|in:' . implode(',', ShippingConstants::CARRIER),
            "shippingMethods.*.default" => 'required|boolean',
            "shippingMethods.*.deleted" => 'nullable|boolean',
            "shippingMethods.*.free_shipping.id" => 'nullable|integer',
            "shippingMethods.*.free_shipping.requirement" => 'nullable|string|in:' . implode(',', array_flip(ShippingConstants::FREE_SHIPPING_REQUIREMENT)),
            "shippingMethods.*.free_shipping.min_order_amount" => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            "shippingMethods.*.free_shipping.min_order_before_coupon" => 'nullable|boolean',
            "shippingMethods.*.flat_rates.id" => 'nullable|integer',
            "shippingMethods.*.flat_rates.taxable" => 'nullable|boolean',
            "shippingMethods.*.flat_rates.cost" => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            "shippingMethods.*.flat_rates.hide_if_more" => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            "shippingMethods.*.flat_rates.hide_if_less" => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            "shippingMethods.*.flat_rates.some_day_delivery" => 'nullable|boolean'
        ];
    }

    public function attributes(): array
    {
        return [
            'country_ids' => 'regions',
            'shippingMethods' => 'shipping methods',
            'shippingMethods.*.name' => "shipping method's name",
            'shippingMethods.*.description' => "shipping method's description",
            'shippingMethods.*.carrier' => "shipping method's carrier",
            'shippingMethods.*.free_shipping.min_order_amount' => "shipping method's free shipping min order amount",
            'shippingMethods.*.flat_rates.cost' => "shipping method's flat rate cost",
            'shippingMethods.*.flat_rates.hide_if_more' => "shipping method's hide if more",
            'shippingMethods.*.flat_rates.hide_if_less' => "shipping method's hide if less"
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
