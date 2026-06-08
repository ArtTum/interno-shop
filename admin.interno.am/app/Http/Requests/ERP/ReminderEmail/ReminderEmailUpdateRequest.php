<?php

namespace App\Http\Requests\ERP\ReminderEmail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReminderEmailUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }


    public function rules(): array
    {
        if ($this->language_id > -1) {
            $rules = [
                'id' => 'required|integer',
                'language_id' => 'required|integer',
                'reminder_email_id' => 'nullable',
                'subject' => 'required',
                'title' => 'required',
                'top_text' => 'required|string',
                'bottom_text' => 'nullable|string',
                'footer_text' => 'nullable|string',
            ];
        } else {
            $rules = [
                'id' => 'required|integer',
                'language_id' => 'required|integer',
                'name' => 'required',
                'order_status' => 'nullable',
                'payment_method' => 'nullable',
                'shipping_method' => 'nullable',
                'time_unit' => 'required|string',
                'newsletter_related' => 'required|boolean',
                'count' => 'required|integer|min:1',
            ];
        }

        return $rules;
    }


    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
