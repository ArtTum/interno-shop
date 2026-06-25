<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Media;
use App\Models\ShopCategory;
use App\Models\ShopProduct;
use App\Models\ShopProductAttributePrice;
use App\Models\ShopProductAttributeValue;
use App\Models\ShopProductColor;
use App\Models\ShopProductOptionType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShopProductSeeder extends Seeder
{
    private const PRODUCT_MULTIPLIER = 4;

    private array $langIds      = [];
    private array $colorIds     = [];
    private array $typeIds      = [];
    private array $attrValueIds = [];

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('shop_product_attribute_prices')->truncate();
        DB::table('shop_product_translations')->truncate();
        DB::table('shop_products')->truncate();
        DB::table('media')->where('type', 'images')->whereRaw("file_name LIKE 'prod-%'")->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->langIds = Language::query()->where('status', true)
            ->pluck('id', 'code')->all();

        $this->colorIds = ShopProductColor::query()
            ->pluck('id', 'name')->all();

        $this->typeIds = ShopProductOptionType::query()
            ->pluck('id', 'name')->all();

        $this->attrValueIds = ShopProductAttributeValue::query()
            ->get(['id', 'type', 'name'])
            ->mapWithKeys(fn ($v) => ["{$v->type}:{$v->name}" => $v->id])
            ->all();

        foreach ($this->expandedProducts() as $i => $def) {
            $this->createProduct($i, $def);
        }
    }

    private function expandedProducts(): array
    {
        $products = [];

        foreach ($this->products() as $def) {
            for ($variant = 0; $variant < self::PRODUCT_MULTIPLIER; $variant++) {
                $products[] = $this->productVariant($def, $variant);
            }
        }

        return $products;
    }

    private function productVariant(array $def, int $variant): array
    {
        if ($variant === 0) {
            return $def;
        }

        $number = $variant + 1;
        $suffixes = [
            'hy' => " տարբերակ {$number}",
            'ru' => " вариант {$number}",
            'en' => " variant {$number}",
        ];

        $def['slug'] = "{$def['slug']}-v{$number}";
        $def['price'] = (int) $def['price'] + ($variant * 750);
        $def['kind'] = trim(($def['kind'] ?? 'product') . "-v{$number}");
        $def['is_new'] = $variant % 2 === 1;

        if (isset($def['options']['code'])) {
            $def['options']['code'] = "{$def['options']['code']}-V{$number}";
        }

        foreach (['titles', 'short'] as $field) {
            foreach ($def[$field] ?? [] as $lang => $value) {
                $def[$field][$lang] = $value . ($suffixes[$lang] ?? " variant {$number}");
            }
        }

        return $def;
    }

    private function createProduct(int $i, array $def): void
    {
        $categoryId = $this->resolveCategoryId($def['category'], $def['child_index'] ?? null);
        $mediaId    = $this->mediaForImage($def['image']);
        $gallery    = $def['gallery'] ?? [$def['image']];
        $galleryIds = array_values(array_filter(array_map(fn ($img) => $this->mediaForImage($img), $gallery)));

        $product = ShopProduct::query()->create([
            'shop_category_id'           => $categoryId,
            'slug'                       => Str::slug($def['slug']),
            'price'                      => $def['price'],
            'kind'                       => $def['kind'] ?? null,
            'media_id'                   => $mediaId,
            'image'                      => '/assets/products/' . $def['image'],
            'gallery'                    => array_map(fn ($img) => '/assets/products/' . $img, $gallery),
            'gallery_media_ids'          => $galleryIds,
            'options'                    => $def['options'] ?? [],
            'option_code'                => $def['options']['code'] ?? null,
            'option_size'                => $def['options']['size'] ?? null,
            'option_quantity'            => $def['options']['quantity'] ?? null,
            'option_type_id'             => isset($def['type']) ? ($this->typeIds[$def['type']] ?? null) : null,
            'option_unit'                => $def['options']['unit'] ?? null,
            'option_piece'               => $def['options']['piece'] ?? null,
            'option_height'              => $def['options']['height'] ?? null,
            'option_material'            => $def['options']['material'] ?? null,
            'option_color_id'            => isset($def['color']) ? ($this->colorIds[$def['color']] ?? null) : null,
            'option_color_ids'           => isset($def['colors'])
                ? array_values(array_filter(array_map(fn ($c) => $this->colorIds[$c] ?? null, $def['colors'])))
                : [],
            'is_new'                     => $def['is_new'] ?? false,
            'is_temporarily_unavailable' => $def['unavailable'] ?? false,
            'status'                     => true,
            'sort_order'                 => $i,
        ]);

        // Attribute prices (height / unit / size / power)
        foreach ($def['attr_prices'] ?? [] as $key => $price) {
            $attrId = $this->attrValueIds[$key] ?? null;
            if (!$attrId) continue;
            ShopProductAttributePrice::query()->create([
                'shop_product_id'                 => $product->id,
                'shop_product_attribute_value_id' => $attrId,
                'price'                           => $price,
            ]);
        }

        // Translations
        foreach ($def['titles'] as $lang => $title) {
            $langId = $this->langIds[$lang] ?? null;
            if (!$langId) continue;
            $product->translations()->create([
                'language_id'       => $langId,
                'title'             => $title,
                'short_description' => $def['short'][$lang] ?? '',
                'description'       => $def['long'][$lang] ?? '',
                'meta_title'        => $title,
                'meta_description'  => $def['short'][$lang] ?? '',
            ]);
        }
    }

    private function resolveCategoryId(string $parentSlug, ?int $childIndex): ?int
    {
        $parent = ShopCategory::query()->where('slug', $parentSlug)->first();
        if (!$parent) return null;
        if ($childIndex === null) return $parent->id;

        $child = ShopCategory::query()
            ->where('parent_id', $parent->id)
            ->where('status', true)
            ->orderBy('sort_order')
            ->get()->values()->get($childIndex);

        return $child?->id ?? $parent->id;
    }

    private function mediaForImage(string $fileName): ?int
    {
        $path = public_path('/assets/products/' . $fileName);
        if (!file_exists($path)) return null;

        $media = Media::query()->updateOrCreate(
            ['file_name' => $fileName],
            [
                'user_id'       => 1,
                'original_path' => '/assets/products/' . $fileName,
                'path'          => null,
                'file_size'     => (int) ceil(filesize($path) / 1024),
                'width'         => 400,
                'height'        => 300,
                'file_type'     => 'image/svg+xml',
                'type'          => 'images',
            ]
        );

        return $media->id;
    }

    // ──────────────────────────────────────────────────────────────
    //  attr_prices keys: "{type}:{attribute name}" => price
    //  Types: height, unit, size, power
    //  Names from ShopProductParametersSeeder:
    //    height: 1 m / 2 m / 2.5 m / 3 m / 4 m
    //    unit:   Piece / Meter / Square meter / Roll / Set
    //    size:   Small / Medium / Large / 10 mm / 20 mm / 40 mm / 60 mm
    //    power:  6 W / 12 W / 18 W / 24 W / 36 W
    // ──────────────────────────────────────────────────────────────
    private function products(): array
    {
        return [

            // ── Stretch ceilings / Profile ──
            [
                'slug'        => 'stretch-profile-white-standard',
                'category'    => 'dzgvvogh-arastaghner',
                'child_index' => 0,
                'price'       => 14500,
                'kind'        => 'white',
                'type'        => 'Profile',
                'color'       => 'White',
                'colors'      => ['White', 'Cream'],
                'is_new'      => true,
                'image'       => 'prod-stretch-profile-white.svg',
                'gallery'     => ['prod-stretch-profile-white.svg', 'prod-stretch-profile-silver.svg'],
                'options'     => ['code' => 'STR-WH-001', 'size' => '3 m', 'height' => '55 mm', 'unit' => 'm', 'piece' => '1', 'type' => 'Standard', 'material' => 'Aluminium', 'color' => '#ffffff'],
                'attr_prices' => ['height:2 m' => 11000, 'height:2.5 m' => 12500, 'height:3 m' => 14500, 'height:4 m' => 17500, 'unit:Meter' => 14500, 'unit:Set' => 56000],
                'titles'      => ['hy' => 'Spitak standart profil dzgvvogh arastagheri hamar', 'ru' => 'Белый стандартный профиль для натяжных потолков', 'en' => 'White standard profile for stretch ceilings'],
                'short'       => ['hy' => 'Alumine standart profil, 3 metr, amur', 'ru' => 'Алюминиевый профиль 3 м для натяжных потолков', 'en' => 'Aluminum standard profile 3m for stretch ceilings'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],
            [
                'slug'        => 'stretch-profile-black-standard',
                'category'    => 'dzgvvogh-arastaghner',
                'child_index' => 0,
                'price'       => 15000,
                'kind'        => 'black',
                'type'        => 'Profile',
                'color'       => 'Black',
                'colors'      => ['Black', 'Anthracite'],
                'is_new'      => false,
                'image'       => 'prod-stretch-profile-black.svg',
                'gallery'     => ['prod-stretch-profile-black.svg', 'prod-stretch-profile-silver.svg'],
                'options'     => ['code' => 'STR-BK-002', 'size' => '3 m', 'height' => '55 mm', 'unit' => 'm', 'piece' => '1', 'type' => 'Standard', 'material' => 'Aluminium', 'color' => '#111111'],
                'attr_prices' => ['height:2 m' => 11500, 'height:2.5 m' => 13000, 'height:3 m' => 15000, 'height:4 m' => 18000, 'unit:Meter' => 15000, 'unit:Set' => 58000],
                'titles'      => ['hy' => 'Sev standart profil dzgvvogh arastagheri hamar', 'ru' => 'Чёрный стандартный профиль для натяжных потолков', 'en' => 'Black standard profile for stretch ceilings'],
                'short'       => ['hy' => 'Matyt sev, minimalistakan dizayn', 'ru' => 'Матовый чёрный, минималистичный дизайн', 'en' => 'Matte black, minimalist design'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],
            [
                'slug'        => 'stretch-profile-silver-premium',
                'category'    => 'dzgvvogh-arastaghner',
                'child_index' => 0,
                'price'       => 17500,
                'kind'        => 'silver',
                'type'        => 'Profile',
                'color'       => 'Silver',
                'colors'      => ['Silver', 'Gray'],
                'is_new'      => true,
                'image'       => 'prod-stretch-profile-silver.svg',
                'gallery'     => ['prod-stretch-profile-silver.svg', 'prod-stretch-profile-white.svg'],
                'options'     => ['code' => 'STR-SV-003', 'size' => '3 m', 'height' => '60 mm', 'unit' => 'm', 'piece' => '1', 'type' => 'Premium', 'material' => 'Aluminium', 'color' => '#c0c0c0'],
                'attr_prices' => ['height:2 m' => 13500, 'height:2.5 m' => 15500, 'height:3 m' => 17500, 'height:4 m' => 21000, 'unit:Meter' => 17500, 'unit:Set' => 68000],
                'titles'      => ['hy' => 'Artcataghaguyn premium profil dzgvvogh arastagheri hamar', 'ru' => 'Серебристый премиум профиль для натяжных потолков', 'en' => 'Silver premium profile for stretch ceilings'],
                'short'       => ['hy' => 'Premium daser, metaxik erevutyun', 'ru' => 'Премиум класс, металлический блеск', 'en' => 'Premium class, metallic shine'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],

            // ── Stretch ceilings / Mesh ──
            [
                'slug'        => 'stretch-mesh-standard',
                'category'    => 'dzgvvogh-arastaghner',
                'child_index' => 1,
                'price'       => 8000,
                'kind'        => 'mesh',
                'type'        => 'Mesh',
                'color'       => 'Gray',
                'is_new'      => false,
                'image'       => 'prod-stretch-mesh-standard.svg',
                'gallery'     => ['prod-stretch-mesh-standard.svg', 'prod-stretch-mesh-fine.svg'],
                'options'     => ['code' => 'MSH-ST-010', 'size' => '1x1 m', 'height' => '2 mm', 'unit' => 'm2', 'piece' => '1', 'type' => 'Standard', 'material' => 'Fiberglass', 'color' => '#808080'],
                'attr_prices' => ['size:Medium' => 8000, 'size:Large' => 11000, 'unit:Square meter' => 8000, 'unit:Set' => 42000],
                'titles'      => ['hy' => 'Standart vaghkap dzgvvogh arastagheri hamar', 'ru' => 'Стандартная сетка для натяжных потолков', 'en' => 'Standard mesh for stretch ceilings'],
                'short'       => ['hy' => 'Amur fiberglass vaghkap 1x1 m', 'ru' => 'Прочная стекловолоконная сетка', 'en' => 'Durable fiberglass mesh'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],
            [
                'slug'        => 'stretch-mesh-fine',
                'category'    => 'dzgvvogh-arastaghner',
                'child_index' => 1,
                'price'       => 10500,
                'kind'        => 'mesh-fine',
                'type'        => 'Mesh',
                'color'       => 'Beige',
                'is_new'      => true,
                'image'       => 'prod-stretch-mesh-fine.svg',
                'gallery'     => ['prod-stretch-mesh-fine.svg', 'prod-stretch-mesh-standard.svg'],
                'options'     => ['code' => 'MSH-FN-011', 'size' => '1x1 m', 'height' => '1 mm', 'unit' => 'm2', 'piece' => '1', 'type' => 'Fine', 'material' => 'Microfiber', 'color' => '#d8c3a5'],
                'attr_prices' => ['size:Small' => 8500, 'size:Medium' => 10500, 'size:Large' => 14000, 'unit:Square meter' => 10500, 'unit:Roll' => 48000],
                'titles'      => ['hy' => 'Nrbagrits premium vaghkap dzgvvogh arastagheri hamar', 'ru' => 'Мелкоячеистая премиум сетка', 'en' => 'Fine premium mesh for stretch ceilings'],
                'short'       => ['hy' => 'Nrbagrits karkasin vaghkap', 'ru' => 'Мелкоячеистая каркасная сетка', 'en' => 'Fine-weave structural mesh'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],

            // ── Stretch ceilings / Wood ──
            [
                'slug'        => 'stretch-wood-oak-panel',
                'category'    => 'dzgvvogh-arastaghner',
                'child_index' => 2,
                'price'       => 22000,
                'kind'        => 'wood',
                'type'        => 'Panel',
                'color'       => 'Wood',
                'is_new'      => false,
                'image'       => 'prod-stretch-wood-oak.svg',
                'gallery'     => ['prod-stretch-wood-oak.svg', 'prod-stretch-wood-walnut.svg'],
                'options'     => ['code' => 'WD-OK-020', 'size' => '120x15 cm', 'height' => '15 mm', 'unit' => 'pcs', 'piece' => '6', 'type' => 'Decorative', 'material' => 'MDF oak', 'color' => '#8B6914'],
                'attr_prices' => ['size:Medium' => 22000, 'size:Large' => 28000, 'unit:Piece' => 22000, 'unit:Set' => 120000],
                'titles'      => ['hy' => 'Kaghnu paytayin panel dzgvvogh arastagheri hamar', 'ru' => 'Дубовая деревянная панель для потолков', 'en' => 'Oak wood panel for stretch ceilings'],
                'short'       => ['hy' => 'Birozakan MDF kaghnu guynov', 'ru' => 'Натуральный вид MDF под дуб', 'en' => 'Natural MDF oak finish'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],
            [
                'slug'        => 'stretch-wood-walnut-panel',
                'category'    => 'dzgvvogh-arastaghner',
                'child_index' => 2,
                'price'       => 24000,
                'kind'        => 'wood-dark',
                'type'        => 'Panel',
                'color'       => 'Brown',
                'is_new'      => true,
                'image'       => 'prod-stretch-wood-walnut.svg',
                'gallery'     => ['prod-stretch-wood-walnut.svg', 'prod-stretch-wood-oak.svg'],
                'options'     => ['code' => 'WD-WL-021', 'size' => '120x15 cm', 'height' => '15 mm', 'unit' => 'pcs', 'piece' => '6', 'type' => 'Decorative', 'material' => 'MDF walnut', 'color' => '#6B3A2A'],
                'attr_prices' => ['size:Medium' => 24000, 'size:Large' => 30000, 'unit:Piece' => 24000, 'unit:Set' => 130000],
                'titles'      => ['hy' => 'Enkuzeni mug panel dzgvvogh arastagheri hamar', 'ru' => 'Тёмная ореховая панель для потолков', 'en' => 'Dark walnut panel for stretch ceilings'],
                'short'       => ['hy' => 'Mug erang, premium MDF enkuzenavayn', 'ru' => 'Тёмный тон, премиум MDF под орех', 'en' => 'Dark tone, premium MDF walnut'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],

            // ── MDF Skirting / White ──
            [
                'slug'        => 'mdf-skirting-white-classic',
                'category'    => 'mdf',
                'child_index' => 0,
                'price'       => 5500,
                'kind'        => 'white',
                'type'        => 'Skirting',
                'color'       => 'White',
                'colors'      => ['White', 'Cream'],
                'is_new'      => false,
                'image'       => 'prod-mdf-white.svg',
                'gallery'     => ['prod-mdf-white.svg', 'prod-mdf-black.svg'],
                'options'     => ['code' => 'MDF-WH-100', 'size' => '240x7 cm', 'height' => '70 mm', 'unit' => 'pcs', 'piece' => '1', 'type' => 'Classic', 'material' => 'MDF', 'color' => '#ffffff'],
                'attr_prices' => ['size:10 mm' => 3500, 'size:20 mm' => 4500, 'size:40 mm' => 5500, 'size:60 mm' => 7000, 'unit:Piece' => 5500, 'unit:Set' => 22000],
                'titles'      => ['hy' => 'Spitak klasik MDF shrishak', 'ru' => 'Белый классический плинтус MDF', 'en' => 'White classic MDF skirting board'],
                'short'       => ['hy' => 'MDF 70 mm spitak, 240 cm', 'ru' => 'MDF 70 мм белый, длина 240 см', 'en' => 'MDF 70mm white, 240cm length'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],
            [
                'slug'        => 'mdf-skirting-white-wide',
                'category'    => 'mdf',
                'child_index' => 0,
                'price'       => 7200,
                'kind'        => 'white',
                'type'        => 'Skirting',
                'color'       => 'Cream',
                'is_new'      => true,
                'image'       => 'prod-mdf-white.svg',
                'gallery'     => ['prod-mdf-white.svg'],
                'options'     => ['code' => 'MDF-WH-101', 'size' => '240x10 cm', 'height' => '100 mm', 'unit' => 'pcs', 'piece' => '1', 'type' => 'Wide', 'material' => 'MDF', 'color' => '#f5f0e6'],
                'attr_prices' => ['size:40 mm' => 5800, 'size:60 mm' => 7200, 'unit:Piece' => 7200, 'unit:Set' => 28000],
                'titles'      => ['hy' => 'Lern spitak MDF shrishak', 'ru' => 'Широкий белый плинтус MDF', 'en' => 'Wide white MDF skirting board'],
                'short'       => ['hy' => 'MDF 100 mm lern format, kam erangi', 'ru' => 'MDF 100 мм широкий, кремовый тон', 'en' => 'MDF 100mm wide, cream tone'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],

            // ── MDF Skirting / Black ──
            [
                'slug'        => 'mdf-skirting-black-classic',
                'category'    => 'mdf',
                'child_index' => 1,
                'price'       => 6000,
                'kind'        => 'black',
                'type'        => 'Skirting',
                'color'       => 'Black',
                'colors'      => ['Black', 'Anthracite'],
                'is_new'      => false,
                'image'       => 'prod-mdf-black.svg',
                'gallery'     => ['prod-mdf-black.svg', 'prod-mdf-white.svg'],
                'options'     => ['code' => 'MDF-BK-110', 'size' => '240x7 cm', 'height' => '70 mm', 'unit' => 'pcs', 'piece' => '1', 'type' => 'Classic', 'material' => 'MDF', 'color' => '#111111'],
                'attr_prices' => ['size:10 mm' => 3800, 'size:20 mm' => 5000, 'size:40 mm' => 6000, 'size:60 mm' => 7500, 'unit:Piece' => 6000, 'unit:Set' => 24000],
                'titles'      => ['hy' => 'Sev klasik MDF shrishak', 'ru' => 'Чёрный классический плинтус MDF', 'en' => 'Black classic MDF skirting board'],
                'short'       => ['hy' => 'MDF 70 mm sev matyt erangi', 'ru' => 'MDF 70 мм матовый чёрный', 'en' => 'MDF 70mm matte black'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],
            [
                'slug'        => 'mdf-skirting-anthracite',
                'category'    => 'mdf',
                'child_index' => 1,
                'price'       => 6500,
                'kind'        => 'anthracite',
                'type'        => 'Skirting',
                'color'       => 'Anthracite',
                'is_new'      => false,
                'image'       => 'prod-mdf-black.svg',
                'gallery'     => ['prod-mdf-black.svg'],
                'options'     => ['code' => 'MDF-AN-111', 'size' => '240x7 cm', 'height' => '70 mm', 'unit' => 'pcs', 'piece' => '1', 'type' => 'Classic', 'material' => 'MDF', 'color' => '#293133'],
                'attr_prices' => ['size:20 mm' => 5200, 'size:40 mm' => 6500, 'size:60 mm' => 8000, 'unit:Piece' => 6500, 'unit:Set' => 26000],
                'titles'      => ['hy' => 'Antrasit MDF shrishak', 'ru' => 'Антрацит плинтус MDF', 'en' => 'Anthracite MDF skirting board'],
                'short'       => ['hy' => 'Antrasit erangi MDF, mug ton', 'ru' => 'Антрацитовый цвет MDF, тёмный тон', 'en' => 'Anthracite MDF, dark tone'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],

            // ── MDF Skirting / Wood ──
            [
                'slug'        => 'mdf-skirting-wood-oak',
                'category'    => 'mdf',
                'child_index' => 2,
                'price'       => 8500,
                'kind'        => 'wood',
                'type'        => 'Skirting',
                'color'       => 'Wood',
                'is_new'      => true,
                'image'       => 'prod-mdf-wood.svg',
                'gallery'     => ['prod-mdf-wood.svg', 'prod-stretch-wood-oak.svg'],
                'options'     => ['code' => 'MDF-WD-120', 'size' => '240x7 cm', 'height' => '70 mm', 'unit' => 'pcs', 'piece' => '1', 'type' => 'Wood', 'material' => 'MDF oak', 'color' => '#b07a4a'],
                'attr_prices' => ['size:20 mm' => 6500, 'size:40 mm' => 8500, 'size:60 mm' => 10500, 'unit:Piece' => 8500, 'unit:Set' => 33000],
                'titles'      => ['hy' => 'Kaghnu erangi paytayin MDF shrishak', 'ru' => 'Деревянный плинтус MDF под дуб', 'en' => 'Wood MDF skirting board oak finish'],
                'short'       => ['hy' => 'MDF kaghnu erangi 70 mm', 'ru' => 'MDF под дуб, 70 мм', 'en' => 'MDF oak finish, 70mm'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],
            [
                'slug'        => 'mdf-skirting-wood-walnut',
                'category'    => 'mdf',
                'child_index' => 2,
                'price'       => 9000,
                'kind'        => 'wood-dark',
                'type'        => 'Skirting',
                'color'       => 'Brown',
                'is_new'      => false,
                'image'       => 'prod-mdf-wood.svg',
                'gallery'     => ['prod-mdf-wood.svg', 'prod-stretch-wood-walnut.svg'],
                'options'     => ['code' => 'MDF-WD-121', 'size' => '240x7 cm', 'height' => '70 mm', 'unit' => 'pcs', 'piece' => '1', 'type' => 'Wood', 'material' => 'MDF walnut', 'color' => '#8b5a2b'],
                'attr_prices' => ['size:20 mm' => 7000, 'size:40 mm' => 9000, 'size:60 mm' => 11000, 'unit:Piece' => 9000, 'unit:Set' => 35000],
                'titles'      => ['hy' => 'Enkuzeni erangi MDF shrishak', 'ru' => 'Плинтус MDF под орех', 'en' => 'Walnut finish MDF skirting board'],
                'short'       => ['hy' => 'MDF enkuzeni erangi, mug ton', 'ru' => 'MDF под орех, тёмный тон', 'en' => 'MDF walnut dark tone'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],

            // ── Aluminum Profile / Corner ──
            [
                'slug'        => 'alu-corner-black-standard',
                'category'    => 'aluminum',
                'child_index' => 0,
                'price'       => 15000,
                'kind'        => 'black',
                'type'        => 'Profile',
                'color'       => 'Black',
                'colors'      => ['Black', 'Anthracite'],
                'is_new'      => false,
                'image'       => 'prod-alu-corner-black.svg',
                'gallery'     => ['prod-alu-corner-black.svg', 'prod-alu-corner-silver.svg'],
                'options'     => ['code' => 'ALU-CRN-BK-200', 'size' => '3 m', 'height' => '50 mm', 'unit' => 'm', 'piece' => '1', 'type' => 'Corner', 'material' => 'Aluminium', 'color' => '#111111'],
                'attr_prices' => ['height:2 m' => 11000, 'height:2.5 m' => 12500, 'height:3 m' => 15000, 'height:4 m' => 18500, 'unit:Meter' => 15000, 'unit:Set' => 60000],
                'titles'      => ['hy' => 'Sev ankghunain alumine profil', 'ru' => 'Чёрный угловой алюминиевый профиль', 'en' => 'Black corner aluminum profile'],
                'short'       => ['hy' => 'Alumine ankghunain profil 3 m, sev', 'ru' => 'Алюминиевый угловой профиль 3 м чёрный', 'en' => 'Aluminum corner profile 3m black'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],
            [
                'slug'        => 'alu-corner-silver-standard',
                'category'    => 'aluminum',
                'child_index' => 0,
                'price'       => 15000,
                'kind'        => 'silver',
                'type'        => 'Profile',
                'color'       => 'Silver',
                'colors'      => ['Silver', 'Gray'],
                'is_new'      => false,
                'image'       => 'prod-alu-corner-silver.svg',
                'gallery'     => ['prod-alu-corner-silver.svg', 'prod-alu-corner-black.svg'],
                'options'     => ['code' => 'ALU-CRN-SV-201', 'size' => '3 m', 'height' => '50 mm', 'unit' => 'm', 'piece' => '1', 'type' => 'Corner', 'material' => 'Aluminium', 'color' => '#c0c0c0'],
                'attr_prices' => ['height:2 m' => 11000, 'height:2.5 m' => 12500, 'height:3 m' => 15000, 'height:4 m' => 18500, 'unit:Meter' => 15000, 'unit:Set' => 60000],
                'titles'      => ['hy' => 'Artcataghaguyn ankghunain alumine profil', 'ru' => 'Серебристый угловой алюминиевый профиль', 'en' => 'Silver corner aluminum profile'],
                'short'       => ['hy' => 'Alumine ankghunain profil 3 m, artcataghaguyn', 'ru' => 'Алюминиевый угловой профиль 3 м серебристый', 'en' => 'Aluminum corner profile 3m silver'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],
            [
                'slug'        => 'alu-corner-gold-premium',
                'category'    => 'aluminum',
                'child_index' => 0,
                'price'       => 19500,
                'kind'        => 'gold',
                'type'        => 'Profile',
                'color'       => 'Gold',
                'is_new'      => true,
                'image'       => 'prod-alu-corner-silver.svg',
                'gallery'     => ['prod-alu-corner-silver.svg'],
                'options'     => ['code' => 'ALU-CRN-GD-202', 'size' => '3 m', 'height' => '50 mm', 'unit' => 'm', 'piece' => '1', 'type' => 'Corner', 'material' => 'Aluminium', 'color' => '#d4af37'],
                'attr_prices' => ['height:2 m' => 15000, 'height:2.5 m' => 17000, 'height:3 m' => 19500, 'height:4 m' => 24000, 'unit:Meter' => 19500, 'unit:Set' => 76000],
                'titles'      => ['hy' => 'Voski ankghunain alumine profil premium', 'ru' => 'Золотой угловой алюминиевый профиль премиум', 'en' => 'Gold corner aluminum profile premium'],
                'short'       => ['hy' => 'Premium voski erangi, luxury interer', 'ru' => 'Золотой тон, для роскошных интерьеров', 'en' => 'Gold tone, for luxury interiors'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],

            // ── Aluminum Profile / LED channel ──
            [
                'slug'        => 'alu-led-channel-silver',
                'category'    => 'aluminum',
                'child_index' => 1,
                'price'       => 18000,
                'kind'        => 'silver',
                'type'        => 'Profile',
                'color'       => 'Silver',
                'is_new'      => true,
                'image'       => 'prod-alu-led-channel.svg',
                'gallery'     => ['prod-alu-led-channel.svg', 'prod-alu-corner-silver.svg'],
                'options'     => ['code' => 'ALU-LED-SV-210', 'size' => '2 m', 'height' => '40 mm', 'unit' => 'm', 'piece' => '1', 'type' => 'LED channel', 'material' => 'Aluminium', 'color' => '#d4d4d4'],
                'attr_prices' => ['height:1 m' => 9500, 'height:2 m' => 18000, 'height:2.5 m' => 21000, 'height:3 m' => 25000, 'unit:Meter' => 18000, 'unit:Set' => 70000],
                'titles'      => ['hy' => 'Artcataghaguyn LED alik alumine profil', 'ru' => 'Серебристый LED канальный алюминиевый профиль', 'en' => 'Silver LED channel aluminum profile'],
                'short'       => ['hy' => 'LED zhapaveni hamar alikayin profil 2 m', 'ru' => 'Каналный профиль для LED ленты 2 м', 'en' => 'Channel profile for LED strip 2m'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],
            [
                'slug'        => 'alu-led-channel-black',
                'category'    => 'aluminum',
                'child_index' => 1,
                'price'       => 18500,
                'kind'        => 'black',
                'type'        => 'Profile',
                'color'       => 'Black',
                'is_new'      => false,
                'image'       => 'prod-alu-led-channel.svg',
                'gallery'     => ['prod-alu-led-channel.svg', 'prod-alu-corner-black.svg'],
                'options'     => ['code' => 'ALU-LED-BK-211', 'size' => '2 m', 'height' => '40 mm', 'unit' => 'm', 'piece' => '1', 'type' => 'LED channel', 'material' => 'Aluminium', 'color' => '#1a1a1a'],
                'attr_prices' => ['height:1 m' => 10000, 'height:2 m' => 18500, 'height:2.5 m' => 22000, 'height:3 m' => 26000, 'unit:Meter' => 18500, 'unit:Set' => 72000],
                'titles'      => ['hy' => 'Sev LED alik alumine profil', 'ru' => 'Чёрный LED канальный алюминиевый профиль', 'en' => 'Black LED channel aluminum profile'],
                'short'       => ['hy' => 'Sev LED alik profil 2 m, minimalistakan', 'ru' => 'Чёрный канальный профиль LED 2 м', 'en' => 'Black LED channel profile 2m'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],

            // ── Aluminum Profile / Mounting ──
            [
                'slug'        => 'alu-mount-profile-black',
                'category'    => 'aluminum',
                'child_index' => 2,
                'price'       => 16000,
                'kind'        => 'black',
                'type'        => 'Mounting part',
                'color'       => 'Black',
                'is_new'      => false,
                'image'       => 'prod-alu-corner-black.svg',
                'gallery'     => ['prod-alu-corner-black.svg'],
                'options'     => ['code' => 'ALU-MT-BK-220', 'size' => '3 m', 'height' => '60 mm', 'unit' => 'm', 'piece' => '1', 'type' => 'Mounting', 'material' => 'Aluminium', 'color' => '#111111'],
                'attr_prices' => ['height:2 m' => 12000, 'height:2.5 m' => 14000, 'height:3 m' => 16000, 'height:4 m' => 19500, 'unit:Meter' => 16000, 'unit:Set' => 63000],
                'titles'      => ['hy' => 'Sev montazhayin alumine profil', 'ru' => 'Чёрный монтажный алюминиевый профиль', 'en' => 'Black mounting aluminum profile'],
                'short'       => ['hy' => 'Montazhi hamar profil, amur amratsm', 'ru' => 'Профиль для монтажа, быстрая установка', 'en' => 'Mounting profile, fast installation'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],
            [
                'slug'        => 'alu-mount-profile-silver',
                'category'    => 'aluminum',
                'child_index' => 2,
                'price'       => 16000,
                'kind'        => 'silver',
                'type'        => 'Mounting part',
                'color'       => 'Silver',
                'is_new'      => true,
                'image'       => 'prod-alu-corner-silver.svg',
                'gallery'     => ['prod-alu-corner-silver.svg'],
                'options'     => ['code' => 'ALU-MT-SV-221', 'size' => '3 m', 'height' => '60 mm', 'unit' => 'm', 'piece' => '1', 'type' => 'Mounting', 'material' => 'Aluminium', 'color' => '#c0c0c0'],
                'attr_prices' => ['height:2 m' => 12000, 'height:2.5 m' => 14000, 'height:3 m' => 16000, 'height:4 m' => 19500, 'unit:Meter' => 16000, 'unit:Set' => 63000],
                'titles'      => ['hy' => 'Artcataghaguyn montazhayin alumine profil', 'ru' => 'Серебристый монтажный алюминиевый профиль', 'en' => 'Silver mounting aluminum profile'],
                'short'       => ['hy' => 'Artcataghaguyn montazhayin profil 3 m', 'ru' => 'Серебристый монтажный профиль 3 м', 'en' => 'Silver mounting profile 3m'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],

            // ── Lighting / LED strip ──
            [
                'slug'        => 'led-strip-warm-5m',
                'category'    => 'lighting',
                'child_index' => 0,
                'price'       => 12000,
                'kind'        => 'led-warm',
                'type'        => 'LED strip',
                'color'       => 'Gold',
                'is_new'      => true,
                'image'       => 'prod-lighting-led-strip.svg',
                'gallery'     => ['prod-lighting-led-strip.svg', 'prod-alu-led-channel.svg'],
                'options'     => ['code' => 'LED-WM-5M-300', 'size' => '5 m', 'height' => '8 mm', 'unit' => 'roll', 'piece' => '1', 'type' => 'Warm white', 'material' => 'LED 2835', 'color' => '#ffcc44'],
                'attr_prices' => ['power:6 W' => 7000, 'power:12 W' => 12000, 'power:18 W' => 17000, 'power:24 W' => 22000, 'unit:Roll' => 12000, 'unit:Meter' => 2500],
                'titles'      => ['hy' => 'Jerm spitak LED zhapaven 5 m', 'ru' => 'Тёплая белая LED лента 5 м', 'en' => 'Warm white LED strip 5m'],
                'short'       => ['hy' => '2835 SMD LED jerm spitak, 5 m, IP20', 'ru' => '2835 SMD LED тёплый белый 5 м IP20', 'en' => '2835 SMD LED warm white 5m IP20'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],
            [
                'slug'        => 'led-strip-cool-5m',
                'category'    => 'lighting',
                'child_index' => 0,
                'price'       => 12000,
                'kind'        => 'led-cool',
                'type'        => 'LED strip',
                'color'       => 'White',
                'is_new'      => false,
                'image'       => 'prod-lighting-led-strip.svg',
                'gallery'     => ['prod-lighting-led-strip.svg'],
                'options'     => ['code' => 'LED-CL-5M-301', 'size' => '5 m', 'height' => '8 mm', 'unit' => 'roll', 'piece' => '1', 'type' => 'Cool white', 'material' => 'LED 2835', 'color' => '#e8f4ff'],
                'attr_prices' => ['power:6 W' => 7000, 'power:12 W' => 12000, 'power:18 W' => 17000, 'power:24 W' => 22000, 'unit:Roll' => 12000, 'unit:Meter' => 2500],
                'titles'      => ['hy' => 'Sarluk spitak LED zhapaven 5 m', 'ru' => 'Холодный белый LED лента 5 м', 'en' => 'Cool white LED strip 5m'],
                'short'       => ['hy' => '2835 SMD LED sarluk spitak, 5 m, IP20', 'ru' => '2835 SMD LED холодный белый 5 м IP20', 'en' => '2835 SMD LED cool white 5m IP20'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],
            [
                'slug'        => 'led-strip-rgb-5m',
                'category'    => 'lighting',
                'child_index' => 0,
                'price'       => 18000,
                'kind'        => 'led-rgb',
                'type'        => 'LED strip',
                'color'       => 'Silver',
                'is_new'      => true,
                'image'       => 'prod-lighting-led-strip.svg',
                'gallery'     => ['prod-lighting-led-strip.svg'],
                'options'     => ['code' => 'LED-RGB-5M-302', 'size' => '5 m', 'height' => '10 mm', 'unit' => 'roll', 'piece' => '1', 'type' => 'RGB', 'material' => 'LED 5050', 'color' => '#ff44aa'],
                'attr_prices' => ['power:12 W' => 13000, 'power:18 W' => 18000, 'power:24 W' => 24000, 'power:36 W' => 32000, 'unit:Roll' => 18000, 'unit:Meter' => 3800],
                'titles'      => ['hy' => 'RGB LED zhapaven 5 m guynain', 'ru' => 'RGB LED лента 5 м цветная', 'en' => 'RGB LED strip 5m color'],
                'short'       => ['hy' => '5050 SMD RGB LED, 16 million guyn', 'ru' => '5050 SMD RGB LED, 16 млн цветов', 'en' => '5050 SMD RGB LED, 16 million colors'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],

            // ── Lighting / Lamps ──
            [
                'slug'        => 'led-lamp-6w-warm',
                'category'    => 'lighting',
                'child_index' => 1,
                'price'       => 4500,
                'kind'        => 'lamp',
                'type'        => 'Accessory',
                'color'       => 'White',
                'is_new'      => false,
                'image'       => 'prod-lighting-lamp.svg',
                'gallery'     => ['prod-lighting-lamp.svg'],
                'options'     => ['code' => 'LMP-6W-WM-310', 'size' => 'GU10', 'height' => '55 mm', 'unit' => 'pcs', 'piece' => '1', 'type' => 'Downlight', 'material' => 'Aluminium+glass', 'color' => '#ffffff', 'quantity' => '6W'],
                'attr_prices' => ['power:6 W' => 4500, 'power:12 W' => 7500, 'power:18 W' => 10500, 'unit:Piece' => 4500, 'unit:Set' => 36000],
                'titles'      => ['hy' => 'LED lamp 6W jerm spitak GU10', 'ru' => 'Светодиодная лампа 6Вт тёплый белый GU10', 'en' => 'LED lamp 6W warm white GU10'],
                'short'       => ['hy' => 'GU10 6W LED lamp, jerm spitak 3000K', 'ru' => 'GU10 6Вт LED тёплый белый 3000K', 'en' => 'GU10 6W LED warm white 3000K'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],
            [
                'slug'        => 'led-lamp-12w-cool',
                'category'    => 'lighting',
                'child_index' => 1,
                'price'       => 7500,
                'kind'        => 'lamp',
                'type'        => 'Accessory',
                'color'       => 'White',
                'is_new'      => true,
                'image'       => 'prod-lighting-lamp.svg',
                'gallery'     => ['prod-lighting-lamp.svg'],
                'options'     => ['code' => 'LMP-12W-CL-311', 'size' => 'GU10', 'height' => '65 mm', 'unit' => 'pcs', 'piece' => '1', 'type' => 'Downlight', 'material' => 'Aluminium+glass', 'color' => '#f0f8ff', 'quantity' => '12W'],
                'attr_prices' => ['power:6 W' => 5000, 'power:12 W' => 7500, 'power:18 W' => 11000, 'power:24 W' => 14500, 'unit:Piece' => 7500, 'unit:Set' => 60000],
                'titles'      => ['hy' => 'LED lamp 12W sarluk spitak GU10', 'ru' => 'Светодиодная лампа 12Вт холодный белый GU10', 'en' => 'LED lamp 12W cool white GU10'],
                'short'       => ['hy' => 'GU10 12W LED sarluk spitak 6500K', 'ru' => 'GU10 12Вт LED холодный белый 6500K', 'en' => 'GU10 12W LED cool white 6500K'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],

            // ── Lighting / Accessories ──
            [
                'slug'        => 'led-connector-standard',
                'category'    => 'lighting',
                'child_index' => 2,
                'price'       => 1200,
                'kind'        => 'accessory',
                'type'        => 'Accessory',
                'color'       => 'White',
                'is_new'      => false,
                'image'       => 'prod-lighting-accessory.svg',
                'gallery'     => ['prod-lighting-accessory.svg'],
                'options'     => ['code' => 'ACC-CON-320', 'size' => '10 mm', 'height' => '5 mm', 'unit' => 'pcs', 'piece' => '10', 'type' => 'Connector', 'material' => 'Plastic', 'color' => '#ffffff'],
                'attr_prices' => ['size:10 mm' => 1200, 'size:20 mm' => 1800, 'size:40 mm' => 2400, 'unit:Piece' => 1200, 'unit:Set' => 9500],
                'titles'      => ['hy' => 'LED zhapaveni connector', 'ru' => 'Коннектор для LED ленты', 'en' => 'LED strip connector'],
                'short'       => ['hy' => 'LED zhapaveni hamar connector, 10 mm, 10 hat', 'ru' => 'Коннектор для LED ленты 10 мм, 10 шт', 'en' => 'LED strip connector 10mm, 10pcs'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],
            [
                'slug'        => 'led-power-supply-24w',
                'category'    => 'lighting',
                'child_index' => 2,
                'price'       => 9500,
                'kind'        => 'accessory',
                'type'        => 'Accessory',
                'color'       => 'Silver',
                'is_new'      => false,
                'image'       => 'prod-lighting-accessory.svg',
                'gallery'     => ['prod-lighting-accessory.svg'],
                'options'     => ['code' => 'ACC-PSU-24W-321', 'size' => '200x40 mm', 'height' => '30 mm', 'unit' => 'pcs', 'piece' => '1', 'type' => 'Power supply', 'material' => 'Aluminium', 'color' => '#c0c0c0', 'quantity' => '24W'],
                'attr_prices' => ['power:6 W' => 5500, 'power:12 W' => 7000, 'power:18 W' => 9500, 'power:24 W' => 12000, 'power:36 W' => 16000, 'unit:Piece' => 9500, 'unit:Set' => 75000],
                'titles'      => ['hy' => 'LED 24W silamotor blok', 'ru' => 'Блок питания LED 24Вт', 'en' => 'LED power supply 24W'],
                'short'       => ['hy' => '24W LED silamotor blok, IP20, alumine', 'ru' => 'Блок питания 24Вт для LED IP20', 'en' => '24W LED driver power supply IP20'],
                'long'        => ['hy' => '', 'ru' => '', 'en' => ''],
            ],
        ];
    }
}
