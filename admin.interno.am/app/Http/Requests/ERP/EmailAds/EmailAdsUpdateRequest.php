<?php

namespace App\Http\Requests\ERP\EmailAds;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class EmailAdsUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'id' => 'required',
            'language_id' => 'integer|required',
            'campaign_id' => 'integer|required',
            'name' => 'string|required|max:250',
            'subject' => 'string|required|max:250',
            'from_name' => 'string|required|max:250',
            'from_email' => 'string|required|max:250',
            'reply_to_email' => 'string|required|max:250',
            'body' => 'string|required',
            'schedule_date' => 'string|required|max:250',
            'schedule_time' => 'string|required|max:250',
            'daily_limit' => 'integer|required',
            'customer_segment_ids' => 'array|required',
            'excluded_customer_segment_ids' => 'array|nullable',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
