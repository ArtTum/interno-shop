<?php

namespace App\Http\Requests\ERP\Trash;

use Illuminate\Foundation\Http\FormRequest;

class TrashFetchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => 'required|integer',
            'per_page' => 'required|integer',
            'ordering_field' => 'required|string',
            'ordering_direction' => 'required|string',
            'search' => 'nullable|string',
        ];
    }
}