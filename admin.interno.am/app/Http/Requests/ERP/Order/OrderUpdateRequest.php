<?php

namespace App\Http\Requests\ERP\Order;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class OrderUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'id' => 'required|integer',
            'status' => 'required|integer',
            'payment_date' => 'nullable',
            'alternative_shipping_old' => 'nullable|string',
            'alternative_shipping' => 'nullable|string',
            'stopped' => 'nullable|boolean',
            'stopped_old' => 'nullable|boolean',
            'express' => 'nullable|boolean',
            'language_id' => 'required|integer',
            'express_old' => 'nullable|boolean',
            'coupon_type' => 'nullable',
            'coupon_amount' => 'nullable',
            'paid_status' => 'nullable',
            'old_paid_status' => 'nullable',
            'warehouse_status' => 'nullable',
            'order_currency_rate' => 'nullable',
            'status_old' => 'required|integer',
            'transaction_id' => 'nullable|string|max:80',
            'transaction_id_old' => 'nullable|string|max:80',
            'editing_order_billing_address' => 'required|array',
            'editing_order_billing_address.changed' => 'nullable|boolean',
            'note' => 'nullable|string|max:1000',
            'editing_order_billing_address.id' => 'required|integer',
            'editing_order_billing_address.order_id' => 'required|integer',
            'editing_order_billing_address.country' => 'required|string|max:80',
            'editing_order_billing_address.country_code' => 'required|string|max:80',
            'editing_order_billing_address.name' => 'required|string|max:50',
            'editing_order_billing_address.last_name' => 'required|string|max:50',
            'editing_order_billing_address.company' => 'nullable|string|max:80',
            'editing_order_billing_address.address' => 'required|string|max:250',
            'editing_order_billing_address.address_2' => 'nullable|string|max:50',
            'editing_order_billing_address.city' => 'required|string|max:80',
            'editing_order_billing_address.zip' => 'required|string|max:30',
            'editing_order_billing_address.state' => 'nullable|string|max:50',
            'editing_order_billing_address.phone' => 'required|string|max:30',
            'editing_order_billing_address.email' => 'required|string|email|max:80',
            'editing_order_billing_address.vat_number' => 'nullable|string|max:80',
            'editing_order_shipping_address' => 'required|array',
            'editing_order_shipping_address.id' => 'required|integer',
            'editing_order_shipping_address.changed' => 'nullable|boolean',
            'editing_order_shipping_address.order_id' => 'required|integer',
            'editing_order_shipping_address.country_code' => 'required|string|max:80',
            'editing_order_shipping_address.country' => 'required|string|max:80',
            'editing_order_shipping_address.name' => 'required|string|max:50',
            'editing_order_shipping_address.last_name' => 'required|string|max:50',
            'editing_order_shipping_address.company' => 'nullable|string|max:80',
            'editing_order_shipping_address.address' => 'required|string|max:250',
            'editing_order_shipping_address.address_2' => 'nullable|string|max:50',
            'editing_order_shipping_address.city' => 'required|string|max:80',
            'editing_order_shipping_address.zip' => 'required|string|max:30',
            'editing_order_shipping_address.state' => 'nullable|string|max:50',
            'total_price' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
            'actual_rate' => 'required|numeric',
            'shipping_price' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'total_shipping_price' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'total_discount_price' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
            'shipping_carrier' => 'required|integer',
            'shared_cart_id' => 'nullable|integer',
        ];

        if ($this->status_old == 3) {
            $rules = array_merge($rules, [
                'total_tax' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
                'total_net_weight' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
                'total_weight' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
                'is_dangerous' => 'nullable|boolean',
                'items_subtotal_tax' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
                'items_subtotal_price' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
                'shipping_subtotal_tax' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
                'shipping_carrier' => 'required|integer',
                'payment_method_parent' => 'nullable|string',
                'payment_method_parent_old' => 'nullable|string',
                'shipping_method_name_base' => 'required|string|max:80',
                'order_items' => 'required|array',
                'order_items.*.id' => 'required|integer',
                'order_items.*.sku' => 'nullable|string',
                'order_items.*.name' => 'nullable|string',
                'order_items.*.quantity' => 'required|integer',
                'order_items.*.price' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
                'order_items.*.tax_price' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
                'order_items.*.subtotal' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
                'order_items.*.total' => 'required|numeric|regex:/^\d+(\.\d+)?$/',
                'order_items.*.changed' => 'nullable|boolean',
                'order_items.*.deleted' => 'nullable|boolean',
                'order_items.*.order_item_parents' => 'nullable|array',
                'order_items.*.order_item_parents.*.id' => 'required|integer',
                'order_items.*.order_item_parents.*.quantity' => 'required|integer',
                'order_items.*.extra_products' => 'nullable|array',
                'order_items.*.extra_products.*.order_item_parents' => 'nullable|array',
                'order_items.*.extra_products.*.order_item_parents.*.id' => 'required|integer',
                'order_items.*.extra_products.*.order_item_parents.*.quantity' => 'required|integer',
            ]);
        }
        return $rules;
    }

    public function attributes(): array
    {
        return [
            'editing_order_billing_address' => 'order billing address',
            'editing_order_billing_address.name' => 'name',
            'editing_order_billing_address.last_name' => 'last name',
            'editing_order_billing_address.email' => 'email',
            'editing_order_billing_address.phone' => 'phone',
            'editing_order_billing_address.company' => 'company',
            'editing_order_billing_address.country' => 'country',
            'editing_order_billing_address.state' => 'state',
            'editing_order_billing_address.city' => 'city',
            'editing_order_billing_address.address' => 'address line 1',
            'editing_order_billing_address.zip' => 'zip',
            'editing_order_billing_address.address_2' => 'address line 2',
            'editing_order_shipping_address' => 'order shipping address',
            'editing_order_shipping_address.name' => 'name',
            'editing_order_shipping_address.last_name' => 'last name',
            'editing_order_shipping_address.company' => 'company',
            'editing_order_shipping_address.country' => 'country',
            'editing_order_shipping_address.state' => 'state',
            'editing_order_shipping_address.city' => 'city',
            'editing_order_shipping_address.address' => 'address line 1',
            'editing_order_shipping_address.zip' => 'zip',
            'editing_order_shipping_address.address_2' => 'address line 2',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
