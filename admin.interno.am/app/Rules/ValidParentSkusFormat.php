<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidParentSkusFormat implements Rule
{
    public function passes($attribute, $value): bool
    {
        return preg_match('/^\d+x\d+(,\d+x\d+)*$/', $value);
    }

    public function message(): string
    {
        return 'Invalid format. Example formats: 9x4064824550895 or 9x4064824550895,9x4064824550888,17x4064824559478,3x4064824008068,6x4064824007641';
    }
}
