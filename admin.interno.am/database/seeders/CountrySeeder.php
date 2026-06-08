<?php

namespace Database\Seeders;

use App\Constants\CountryConstants;
use App\Models\AllCountry;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        foreach (CountryConstants::COUNTRIES_TRANSLATION['en'] as $code => $item) {
            AllCountry::updateOrCreate(
                [
                    'code' => $code,
                ],
                [
                    'name' => $item
                ]
            );
        }
    }
}
