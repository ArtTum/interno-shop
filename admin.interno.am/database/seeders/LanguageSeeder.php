<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    const ARRAY = [
        'en' => [
            'name' => 'English',
        ],
        'de' => [
            'name' => 'Deutsch',
        ],
        'it' => [
            'name' => 'Italiano',
        ],
        'nl' => [
            'name' => 'Nederlands',
        ],
        'dk' => [
            'name' => 'Dansk',
        ],
        'hu' => [
            'name' => 'Magyar',
        ],
        'hr' => [
            'name' => 'Hrvatski',
        ],
        'pt' => [
            'name' => 'Portugal',
        ],
        'us' => [
            'name' => 'English (US)',
        ],
        'fr' => [
            'name' => 'Français',
        ],
        'es' => [
            'name' => 'Español',
        ],
        'se' => [
            'name' => 'Svenska',
        ],
        'pl' => [
            'name' => 'Polskie',
        ],
        'cz' => [
            'name' => 'Čeština',
        ],
        'sk' => [
            'name' => 'Slovenský',
        ],
    ];

    public function run(): void
    {
        foreach (self::ARRAY as $code => $item) {
            Language::updateOrCreate(
                [
                    'code' => $code,
                ],
                [
                    'name' => $item['name'],
                    'hreflang' => $code,
                    'base' => $code === 'en'
                ]
            );
        }
    }
}
