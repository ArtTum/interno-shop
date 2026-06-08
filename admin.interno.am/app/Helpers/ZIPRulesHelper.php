<?php

if (!function_exists('is_zip_in_array')) {
    function is_zip_in_array(string $zipCode, array $zipArray): bool
    {
        foreach ($zipArray as $entry) {
            if (str_contains($entry, '-')) {
                [$start, $end] = explode('-', $entry);
                if ($zipCode >= $start && $zipCode <= $end) {
                    return true;
                }
            } else {
                if ($zipCode == $entry) {
                    return true;
                }
            }
        }
        return false;
    }
}

if (!function_exists('is_postcode_valid')) {
    function is_postcode_valid(string $postcode, string $country): bool
    {
        if (strlen(trim(preg_replace('/[\s\-A-Za-z0-9]/', '', $postcode))) > 0) return false;

        return match ($country) {
            'AT', 'BE', 'CH', 'HU', 'NO' => (bool)preg_match('/^([0-9]{4})$/', $postcode),
            'BA' => (bool)preg_match('/^([7-8]{1})([0-9]{4})$/', $postcode),
            'BR' => (bool)preg_match('/^([0-9]{5})([-])?([0-9]{3})$/', $postcode),
            'DE' => (bool)preg_match('/^([0]{1}[1-9]{1}|[1-9]{1}[0-9]{1})[0-9]{3}$/', $postcode),
            'DK' => (bool)preg_match('/^(DK-)?([1-24-9]\d{3}|3[0-8]\d{2})$/', $postcode),
            'ES', 'FR', 'IT' => (bool)preg_match('/^([0-9]{5})$/i', $postcode),
            'GB' => is_gb_postcode_valid($postcode),
            'IE' => (bool)preg_match('/([AC-FHKNPRTV-Y]\d{2}|D6W)[0-9AC-FHKNPRTV-Y]{4}/', normalize_postcode($postcode)),
            'IN' => (bool)preg_match('/^[1-9]{1}[0-9]{2}\s{0,1}[0-9]{3}$/', $postcode),
            'JP' => (bool)preg_match('/^([0-9]{3})([-]?)([0-9]{4})$/', $postcode),
            'PT' => (bool)preg_match('/^([0-9]{4})([-])([0-9]{3})$/', $postcode),
            'PR', 'US' => (bool)preg_match('/^([0-9]{5})(-[0-9]{4})?$/i', $postcode),
            'CA' => (bool)preg_match('/^([ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ])([\ ])?(\d[ABCEGHJKLMNPRSTVWXYZ]\d)$/i', $postcode),
            'PL' => (bool)preg_match('/^([0-9]{2})([-])([0-9]{3})$/', $postcode),
            'CZ', 'SK' => (bool)preg_match("/^($country-)?([0-9]{3})(\s?)([0-9]{2})$/", $postcode),
            'NL' => (bool)preg_match('/^([1-9][0-9]{3})(\s?)(?!SA|SD|SS)[A-Z]{2}$/i', $postcode),
            'SI' => (bool)preg_match('/^([1-9][0-9]{3})$/', $postcode),
            'LI' => (bool)preg_match('/^(94[8-9][0-9])$/', $postcode),
            default => true,
        };
    }
}

if (!function_exists('is_gb_postcode_valid')) {
    function is_gb_postcode_valid(string $to_check): bool
    {
        // https://en.wikipedia.org/wiki/Postcodes_in_the_United_Kingdom#Validation.
        $alpha1 = '[abcdefghijklmnoprstuwyz]'; // Character 1.
        $alpha2 = '[abcdefghklmnopqrstuvwxy]'; // Character 2.
        $alpha3 = '[abcdefghjkpstuw]';         // Character 3 == ABCDEFGHJKPSTUW.
        $alpha4 = '[abehmnprvwxy]';            // Character 4 == ABEHMNPRVWXY.
        $alpha5 = '[abdefghjlnpqrstuwxyz]';    // Character 5 != CIKMOV.

        $pcexp = array();

        // Expression for postcodes: AN NAA, ANN NAA, AAN NAA, and AANN NAA.
        $pcexp[0] = '/^(' . $alpha1 . '{1}' . $alpha2 . '{0,1}[0-9]{1,2})([0-9]{1}' . $alpha5 . '{2})$/';

        // Expression for postcodes: ANA NAA.
        $pcexp[1] = '/^(' . $alpha1 . '{1}[0-9]{1}' . $alpha3 . '{1})([0-9]{1}' . $alpha5 . '{2})$/';

        // Expression for postcodes: AANA NAA.
        $pcexp[2] = '/^(' . $alpha1 . '{1}' . $alpha2 . '[0-9]{1}' . $alpha4 . ')([0-9]{1}' . $alpha5 . '{2})$/';

        // Exception for the special postcode GIR 0AA.
        $pcexp[3] = '/^(gir)(0aa)$/';

        // Standard BFPO numbers.
        $pcexp[4] = '/^(bfpo)([0-9]{1,4})$/';

        // c/o BFPO numbers.
        $pcexp[5] = '/^(bfpo)(c\/o[0-9]{1,3})$/';

        // Load up the string to check, converting into lowercase and removing spaces.
        $postcode = strtolower($to_check);
        $postcode = str_replace(' ', '', $postcode);

        // Assume we are not going to find a valid postcode.
        $valid = false;

        // Check the string against the six types of postcodes.
        foreach ($pcexp as $regexp) {
            if (preg_match($regexp, $postcode, $matches)) {
                // Remember that we have found that the code is valid and break from loop.
                $valid = true;
                break;
            }
        }

        return $valid;
    }
}

if (!function_exists('normalize_postcode')) {
    function normalize_postcode(string $postcode): array|string|null
    {
        return preg_replace('/[\s\-]/', '', trim(strtoupper($postcode)));
    }
}

if (!function_exists('format_postcode')) {
    function format_postcode(string $postcode, string $country): string
    {
        $postcode = normalize_postcode($postcode);

        switch ($country) {
            case 'SE':
                $postcode = substr_replace($postcode, ' ', -2, 0);
                break;
            case 'CA':
            case 'GB':
                $postcode = substr_replace($postcode, ' ', -3, 0);
                break;
            case 'IE':
                $postcode = substr_replace($postcode, ' ', 3, 0);
                break;
            case 'BR':
            case 'PL':
                $postcode = substr_replace($postcode, '-', -3, 0);
                break;
            case 'JP':
                $postcode = substr_replace($postcode, '-', 3, 0);
                break;
            case 'PT':
                $postcode = substr_replace($postcode, '-', 4, 0);
                break;
            case 'PR':
            case 'US':
                $postcode = rtrim(substr_replace($postcode, '-', 5, 0), '-');
                break;
            case 'NL':
                $postcode = substr_replace($postcode, ' ', 4, 0);
                break;
            case 'LV':
                $postcode = preg_replace('/^(LV)?-?(\d+)$/', 'LV-${2}', $postcode);
                break;
            case 'CZ':
            case 'SK':
                $postcode = preg_replace("/^({$country})-?(\d+)$/", '${1}-${2}', $postcode);
                $postcode = substr_replace($postcode, ' ', -2, 0);
                break;
            case 'DK':
                $postcode = preg_replace('/^(DK)(.+)$/', '${1}-${2}', $postcode);
                break;
        }

        return trim($postcode);
    }
}
