<?php

namespace Database\Seeders;

use App\Models\MediaSetting;
use Illuminate\Database\Seeder;

class MediaSettingSeeder extends Seeder
{
    const ARRAY = [
        [
            'name' => 'Maximum',
            'width' => '2560',
            'height' => '2560',
        ],
        [
            'name' => 'Large',
            'width' => '1024',
            'height' => '1500',
        ],
        [
            'name' => 'Medium',
            'width' => '640',
            'height' => '800',
        ],
        [
            'name' => 'Thumbnail',
            'width' => '300',
            'height' => '200',
        ],
        [
            'name' => 'Base',
            'width' => '680',
            'height' => '850',
        ],
    ];

    public function run(): void
    {
        foreach (self::ARRAY as $item) {
            MediaSetting::updateOrCreate(
                [
                    'name' => $item['name'],
                ],
                [
                    'width' => $item['width'],
                    'height' => $item['height'],
                ]
            );
        }
    }
}
