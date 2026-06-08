<?php

namespace Database\Seeders;

use App\Models\DocumentSetting;
use Illuminate\Database\Seeder;

class DocumentSettingSeeder extends Seeder
{
    const ARRAY = [
        [
            'name' => 'Invoice',
        ],
        [
            'name' => 'Packing slip',
        ],
        [
            'name' => 'Proforma',
        ],
        [
            'name' => 'Credit note',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::ARRAY as $item) {
            DocumentSetting::create($item);
        }
    }
}
