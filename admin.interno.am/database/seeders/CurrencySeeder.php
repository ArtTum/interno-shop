<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    const ARRAY = [
        [
            'name' => 'Euro',
            'symbol' => '€',
            'code' => 'EUR',
            'rate' => '1',
        ],
        [
            'name' => 'Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'rate' => '1.3',
        ],
        [
            'name' => 'Pound Sterling',
            'code' => 'GBP',
            'symbol' => '£',
            'rate' => '0.9',
        ],
        [
            'name' => 'Franc',
            'code' => 'CHF',
            'symbol' => '₣',
            'rate' => '0.8',
        ],
    ];
    public function run(): void
    {
        foreach (self::ARRAY as $item) {
            Currency::updateOrCreate(
                [
                    'name' => $item['name'],
                ],
                [
                    'symbol' => $item['symbol'],
                    'rate' => $item['rate'],
                    'code' => $item['code'],
                    'base' => $item['name'] === 'Euro'
                ]
            );
        }
    }
}
