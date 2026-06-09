<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_product_option_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('status')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('shop_product_colors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('value', 30)->nullable();
            $table->boolean('status')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::table('shop_products', function (Blueprint $table) {
            $table->string('option_code')->nullable()->after('options');
            $table->string('option_size')->nullable()->after('option_code');
            $table->string('option_quantity')->nullable()->after('option_size');
            $table->foreignId('option_type_id')->nullable()->after('option_quantity')->constrained('shop_product_option_types')->nullOnDelete();
            $table->string('option_unit')->nullable()->after('option_type_id');
            $table->string('option_piece')->nullable()->after('option_unit');
            $table->string('option_height')->nullable()->after('option_piece');
            $table->string('option_material')->nullable()->after('option_height');
            $table->foreignId('option_color_id')->nullable()->after('option_material')->constrained('shop_product_colors')->nullOnDelete();
        });

        $now = now();
        $products = DB::table('shop_products')->get(['id', 'options']);

        foreach ($products as $product) {
            $options = json_decode((string) $product->options, true);

            if (!is_array($options)) {
                continue;
            }

            $typeId = $this->findOrCreateType($options['type'] ?? null, $now);
            $colorId = $this->findOrCreateColor($options['color'] ?? null, $now);

            DB::table('shop_products')
                ->where('id', $product->id)
                ->update([
                    'option_code' => $options['code'] ?? null,
                    'option_size' => $options['size'] ?? null,
                    'option_quantity' => $options['quantity'] ?? null,
                    'option_type_id' => $typeId,
                    'option_unit' => $options['unit'] ?? null,
                    'option_piece' => $options['piece'] ?? null,
                    'option_height' => $options['height'] ?? null,
                    'option_material' => $options['material'] ?? null,
                    'option_color_id' => $colorId,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('option_type_id');
            $table->dropConstrainedForeignId('option_color_id');
            $table->dropColumn([
                'option_code',
                'option_size',
                'option_quantity',
                'option_unit',
                'option_piece',
                'option_height',
                'option_material',
            ]);
        });

        Schema::dropIfExists('shop_product_colors');
        Schema::dropIfExists('shop_product_option_types');
    }

    private function findOrCreateType(?string $name, mixed $now): ?int
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        $existingId = DB::table('shop_product_option_types')->where('name', $name)->value('id');

        if ($existingId) {
            return (int) $existingId;
        }

        return (int) DB::table('shop_product_option_types')->insertGetId([
            'name' => $name,
            'status' => true,
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function findOrCreateColor(?string $value, mixed $now): ?int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $existingId = DB::table('shop_product_colors')
            ->where('value', $value)
            ->orWhere('name', $value)
            ->value('id');

        if ($existingId) {
            return (int) $existingId;
        }

        return (int) DB::table('shop_product_colors')->insertGetId([
            'name' => $value,
            'value' => str_starts_with($value, '#') ? $value : null,
            'status' => true,
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
};
