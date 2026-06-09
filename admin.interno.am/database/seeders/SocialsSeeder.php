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
            'url' => 'https://www.facebook.com/',
            'icon' => 'facebook',
            'color' => '#1877F2',
            'show_in_frontend' => true,
        ],
        [
            'name' => 'Instagram',
            'url' => 'https://www.instagram.com/',
            'icon' => 'instagram',
            'color' => '#E1306C',
            'show_in_frontend' => true,
        ],
        [
            'name' => 'Pinterest',
            'url' => null,
            'icon' => 'pinterest',
            'color' => '#E60023',
            'show_in_frontend' => false,
        ],
        [
            'name' => 'Youtube',
            'url' => null,
            'icon' => 'youtube',
            'color' => '#FF0000',
            'show_in_frontend' => false,
        ],
        [
            'name' => 'Tiktok',
            'url' => null,
            'icon' => 'tiktok',
            'color' => '#69C9D0',
            'show_in_frontend' => false,
        ],
    ];

    public function run(): void
    {
        foreach (self::ARRAY as $index => $item) {
            Social::updateOrCreate(
                [
                    'icon' => $item['icon'],
                ],
                [
                    'name' => $item['name'],
                    'url' => $item['url'],
                    'color' => $item['color'],
                    'status' => true,
                    'show_in_frontend' => $item['show_in_frontend'],
                    'sort_order' => $index,
                ]
            );
        }
    }
}
