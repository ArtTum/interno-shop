<?php

namespace App\Http\Requests\ERP\Order;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class OrderFetchRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'per_page' => 'integer|required',
            'page' => 'integer|required',
            'search' => 'string|nullable',
            'ordering_field' => 'string|nullable',
            'ordering_direction' => 'string|nullable|in:asc,desc',
            'status' => 'array',
            'dispute' => 'required|integer',
            'orders_from' => 'required|integer',
            'warehouse_status' => 'required|integer',
            'reshipment' => 'required|integer',
            'refunded' => 'required|integer',
            'vat_exists' => 'required|integer',
            'user_id' => 'required|integer',
            'used_coupon' => 'required|integer',
            'same_day_delivery' => 'required|integer',
            'only_actuals' => 'required|integer',
            'language_id' => 'required|integer',
            'ordered_by' => 'required|integer',
            'product_id' => 'nullable|integer',
            'shipping_country' => 'nullable|string',
            'billing_country' => 'nullable|string',
            'payment_method_child' => 'nullable|string',
            'payment_method_parent' => 'nullable|string',
            'order_id' => 'nullable|string',
            'invoice_number' => 'nullable|string',
            'old_order_number' => 'nullable|string',
            'product_attribute_ids' => 'nullable|array',
            'payment_method_child_base' => 'nullable|string',
            'total_price_from' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'total_price_to' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'total_discount_price_from' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'total_discount_price_to' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'shipping_carrier' => 'nullable',
            'shipping_label' => 'nullable',
            'payment_date' => 'nullable',
            'invalid_items' => 'nullable',
            'created_at_from' => 'string|nullable',
            'created_at_to' => 'string|nullable',
            'exported_taxfile' => 'required|integer',
            'shared_cart' => 'required|integer',
            'need_adb' => 'required|integer',
            'source' => 'integer|nullable',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'invalid_items' => filter_var($this->invalid_items, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
