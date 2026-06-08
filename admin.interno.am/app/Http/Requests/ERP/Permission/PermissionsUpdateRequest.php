<?php

namespace App\Http\Requests\ERP\Permission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PermissionsUpdateRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules() : array
    {
        return [
            'params.*.id' => 'required|integer',
            'params.*.can_view' => 'boolean|required',
            'params.*.can_edit' => 'boolean|required',
            'params.*.can_delete' => 'boolean|required',
            'params.*.can_upload' => 'boolean|required',
            'params.*.can_export' => 'boolean|required',
            'params.*.can_add' => 'boolean|required',
            'params.*.changed' => 'boolean|required'
        ];
    }

    public function attributes(): array
    {
        return [

        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 500));
    }
}
