<?php

namespace App\Http\Requests\ERP\Feed;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class FeedUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        $id = $this->route('id') ?? $this->input('id');
        return [
            'id' => 'required',
            'language_id' => 'integer|required',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:feed_types,slug,' . $id,
            'separator' => 'nullable|string|max:10',
            'price_separator' => 'nullable|string|max:10',
            'variants_export_into_feed' => 'nullable|boolean',
            'feeds' => 'required|array',
            "feeds.*.id" => 'nullable|integer',
            "feeds.*.priority" => 'nullable',
            "feeds.*.feed_type_id" => 'required|integer',
            "feeds.*.column_name" => 'nullable|string',
            "feeds.*.field_type" => 'nullable|string',
            "feeds.*.sku_prefix" => 'nullable|string',
            "feeds.*.custom_key" => 'nullable|string',
            "feeds.*.custom_value" => 'nullable|string',
            "feeds.*.in_stock_value" => 'nullable|string',
            "feeds.*.out_of_stock_value" => 'nullable|string',
            "feeds.*.chars_limit" => 'nullable|integer',
            "feeds.*.include_sub_name" => 'nullable|boolean',
            "feeds.*.square_image_ratio" => 'nullable|boolean',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
