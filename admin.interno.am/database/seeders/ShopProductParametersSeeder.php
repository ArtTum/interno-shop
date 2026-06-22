<?php

namespace Database\Seeders;

use App\Models\ShopProductAttributeValue;
use App\Models\ShopProductColor;
use App\Models\ShopProductOptionType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ShopProductParametersSeeder extends Seeder
{
    private const OPTION_TYPES = [
        'Profile',
        'Panel',
        'Skirting',
        'Tube',
        'Mesh',
        'LED strip',
        'Accessory',
        'Mounting part',
    ];

    private const COLORS = [
        ['name' => 'White', 'value' => '#ffffff'],
        ['name' => 'Black', 'value' => '#111111'],
        ['name' => 'Silver', 'value' => '#c0c0c0'],
        ['name' => 'Gray', 'value' => '#808080'],
        ['name' => 'Anthracite', 'value' => '#293133'],
        ['name' => 'Cream', 'value' => '#f5f0e6'],
        ['name' => 'Beige', 'value' => '#d8c3a5'],
        ['name' => 'Brown', 'value' => '#8b5a2b'],
        ['name' => 'Gold', 'value' => '#d4af37'],
        ['name' => 'Wood', 'value' => '#b07a4a'],
    ];

    private const ATTRIBUTE_VALUES = [
        'height' => [
            ['name' => '1 m', 'value' => '1m'],
            ['name' => '2 m', 'value' => '2m'],
            ['name' => '2.5 m', 'value' => '2.5m'],
            ['name' => '3 m', 'value' => '3m'],
            ['name' => '4 m', 'value' => '4m'],
        ],
        'unit' => [
            ['name' => 'Piece', 'value' => 'pcs'],
            ['name' => 'Meter', 'value' => 'm'],
            ['name' => 'Square meter', 'value' => 'm2'],
            ['name' => 'Roll', 'value' => 'roll'],
            ['name' => 'Set', 'value' => 'set'],
        ],
        'size' => [
            ['name' => 'Small', 'value' => 'S'],
            ['name' => 'Medium', 'value' => 'M'],
            ['name' => 'Large', 'value' => 'L'],
            ['name' => '10 mm', 'value' => '10mm'],
            ['name' => '20 mm', 'value' => '20mm'],
            ['name' => '40 mm', 'value' => '40mm'],
            ['name' => '60 mm', 'value' => '60mm'],
        ],
        'power' => [
            ['name' => '6 W', 'value' => '6W'],
            ['name' => '12 W', 'value' => '12W'],
            ['name' => '18 W', 'value' => '18W'],
            ['name' => '24 W', 'value' => '24W'],
            ['name' => '36 W', 'value' => '36W'],
        ],
    ];

    public function run(): void
    {
        $this->seedOptionTypes();
        $this->seedColors();
        $this->seedAttributeValues();
    }

    private function seedOptionTypes(): void
    {
        if (!Schema::hasTable('shop_product_option_types')) {
            return;
        }

        foreach (self::OPTION_TYPES as $index => $name) {
            ShopProductOptionType::query()->updateOrCreate(
                ['name' => $name],
                [
                    'status' => true,
                    'sort_order' => $index,
                ]
            );
        }
    }

    private function seedColors(): void
    {
        if (!Schema::hasTable('shop_product_colors')) {
            return;
        }

        foreach (self::COLORS as $index => $color) {
            ShopProductColor::query()->updateOrCreate(
                ['name' => $color['name']],
                [
                    'value' => $color['value'],
                    'status' => true,
                    'sort_order' => $index,
                ]
            );
        }
    }

    private function seedAttributeValues(): void
    {
        if (!Schema::hasTable('shop_product_attribute_values')) {
            return;
        }

        foreach (self::ATTRIBUTE_VALUES as $type => $items) {
            foreach ($items as $index => $item) {
                ShopProductAttributeValue::query()->updateOrCreate(
                    [
                        'type' => $type,
                        'name' => $item['name'],
                    ],
                    [
                        'value' => $item['value'],
                        'status' => true,
                        'sort_order' => $index,
                    ]
                );
            }
        }
    }
}
