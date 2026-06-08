<?php

namespace Database\Seeders;

use App\Models\Social;
use Illuminate\Database\Seeder;

class SocialsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    const ARRAY = [
        [
            'name' => 'Facebook',
            'icon' => 'facebook',
            'color' => '#1877F2',
        ],
        [
            'name' => 'Instagram',
            'icon' => 'instagram',
            'color' => '#E1306C',
        ],
        [
            'name' => 'Pinterest',
            'icon' => 'pinterest',
            'color' => '#E60023',
        ],
        [
            'name' => 'Youtube',
            'icon' => 'youtube',
            'color' => '#FF0000',
        ],
        [
            'name' => 'Tiktok',
            'icon' => 'tiktok',
            'color' => '#69C9D0',
        ],
    ];

    public function run(): void
    {
        foreach (self::ARRAY as $item) {
            Social::updateOrCreate(
                [
                    'icon' => $item['icon'],
                ],
                [
                    'name' => $item['name'],
                    'color' => $item['color'],
                ]
            );
        }
    }
}
