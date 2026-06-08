<?php

namespace App\Rules;

use App\Models\Item;
use Illuminate\Contracts\Validation\Rule;

class ValidParentSkusExist implements Rule
{
    protected array $missingSkus = [];

    public function passes($attribute, $value): bool
    {
        preg_match_all('/(?<=x)\d+/', $value, $matches);
        $skus = $matches[0];

        if (empty($skus)) {
            return false;
        }

        $existingSkus = Item::select('sku')->whereIn('sku', $skus)->pluck('sku')->toArray();

        $this->missingSkus = array_diff($skus, $existingSkus);

        $finalArray = [];
        if (!empty($this->missingSkus)) {
            foreach ($this->missingSkus as $missingSku) {
                if ($missingSku !== '0000000000000') {
                    $finalArray = $missingSku;
                }
            }
        }

        return empty($finalArray);
    }

    public function message(): string
    {
        return 'The following SKUs do not exist in items: ' . implode(', ', $this->missingSkus);
    }
}
