<?php

namespace App\Http\Requests\ERP\ShippingLabelSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ShippingLabelSettingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer',
            'api_key' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'required|string|max:255',
            'billing_number' => 'nullable|string|max:255',
            'billing_number_international' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'meter_number' => 'nullable|string|max:255',
            'pdf_page_size' => 'nullable|string|max:255',
            'country_of_origin' => 'nullable|string|max:255',
            'label_start_position' => 'nullable|string|max:50',
            'parcel_content' => 'nullable|string|max:255',
            'parcel_max_weight' => 'nullable|integer',
            'envelope_max_weight' => 'nullable|integer',
            'reference_1' => 'nullable|string|max:255',
            'reference_2' => 'nullable|string|max:255',
            'predict_function' => 'nullable|boolean',
            'generate_label_on_status_process' => 'nullable|boolean',
            'delivery_terms' => 'nullable|string|max:5',
            'gtnr' => 'nullable|string|max:255',
            'goods_content' => 'nullable|string|max:255',
            'time_from' => 'nullable',
            'time_to' => 'nullable',
            'sender_company' => 'nullable|string|max:255',
            'sender_street' => 'nullable|string|max:255',
            'sender_country' => 'nullable|string|max:20',
            'sender_state' => 'nullable|string|max:20',
            'sender_zip' => 'nullable|string|max:20',
            'sender_city' => 'nullable|string|max:255',
            'sender_phone' => 'nullable|string|max:255',
            'sender_email' => 'nullable|email|max:255',
            'sender_contact_person' => 'nullable|string|max:255',
            'sender_customer_no' => 'nullable|max:255',
            'customs_countries' => 'nullable|array',
            'token' => 'nullable|json',
            'enable_nf4_files' => 'nullable',
            'tnt_consignment_number' => 'nullable',
            'source_code' => 'nullable',
            'sftp_host' => 'nullable',
            'sftp_username' => 'nullable',
            'sftp_password' => 'nullable',
            'from_email_language_id' => 'nullable',
            'to_email' => 'nullable',
            'to_admin_email' => 'nullable',
            'email_subject' => 'nullable',
            'prefix_before_invoice_num' => 'nullable',
            'deleted_labels_text' => 'nullable',
            'combine_emails_pdfs' => 'nullable',
            'use_imo' => 'nullable',
            'eori_number' => 'nullable',
            'adb_enable_price' => 'nullable',
            'packaging_type' => 'nullable',
            'type_of_export' => 'nullable',
            'media_id' => 'nullable',
            'adb_from_email_language_id' => 'nullable',
            'rules' => 'nullable',
            'adb_to_email' => 'nullable',
            'adb_to_admin_email' => 'nullable',
            'adb_email_subject' => 'nullable',
            'default_incoterm' => 'nullable',
            'dap_label_text' => 'nullable',
            'ddp_label_text' => 'nullable',
            'dap_label_text_uk' => 'nullable',
            'ddp_label_text_uk' => 'nullable',

            'shipping_label_setting_id' => 'nullable',
            'use_collection_data' => 'nullable',
            'collection_company' => 'nullable',
            'collection_address_1' => 'nullable',
            'collection_address_2' => 'nullable',
            'collection_city' => 'nullable',
            'collection_zip' => 'nullable',
            'collection_country' => 'nullable',
            'collection_phone' => 'nullable',
            'collection_email' => 'nullable',
            'collection_contact_person' => 'nullable',
            'collection_vat' => 'nullable',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
