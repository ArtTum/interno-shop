<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidCouponAllowedEmailsFormat implements Rule
{
    public function passes($attribute, $value): bool
    {
        $emails = explode('|', $value);

        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
        }

        return true;
    }

    public function message(): string
    {
        return 'The :attribute must be a valid string of email addresses separated by "|".';
    }
}
