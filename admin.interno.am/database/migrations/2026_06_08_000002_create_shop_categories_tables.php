<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('shop_categories') || Schema::hasTable('shop_category_translations')) {
            $categoryRows = Schema::hasTable('shop_categories') ? DB::table('shop_categories')->count() : 0;
            $translationRows = Schema::hasTable('shop_category_translations') ? DB::table('shop_category_translations')->count() : 0;

            if ($categoryRows > 0 || $translationRows > 0) {
                throw new RuntimeException('shop category tables already exist with data; refusing to drop them automatically.');
            }

            Schema::dropIfExists('shop_category_translations');
            Schema::dropIfExists('shop_categories');
        }

        Schema::create('shop_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('shop_categories')->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->boolean('status')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('shop_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_category_id')->constrained('shop_categories')->cascadeOnDelete();
            $table->unsignedInteger('language_id');
            $table->string('title');
            $table->timestamps();

            $table->unique(['shop_category_id', 'language_id'], 'shop_category_language_unique');
            $table->foreign('language_id', 'shop_category_language_fk')->references('id')->on('languages')->cascadeOnDelete();
        });

        $languageIds = DB::table('languages')->pluck('id', 'code');

        if ($languageIds->isNotEmpty()) {
            $categories = [
                [
                    'slug' => 'stretch',
                    'title' => ['hy' => 'Stretch ceilings', 'en' => 'Stretch ceilings', 'ru' => 'Натяжные потолки'],
                    'children' => [
                        ['hy' => 'Profile', 'en' => 'Profile', 'ru' => 'Профиль'],
                        ['hy' => 'Mesh', 'en' => 'Mesh', 'ru' => 'Сетка'],
                        ['hy' => 'Wood material', 'en' => 'Wood material', 'ru' => 'Древесина'],
                        ['hy' => 'Tube', 'en' => 'Tube', 'ru' => 'Труба'],
                    ],
                ],
                [
                    'slug' => 'mdf',
                    'title' => ['hy' => 'MDF skirting', 'en' => 'MDF skirting', 'ru' => 'МДФ плинтус'],
                    'children' => [
                        ['hy' => 'White', 'en' => 'White', 'ru' => 'Белый'],
                        ['hy' => 'Black', 'en' => 'Black', 'ru' => 'Черный'],
                        ['hy' => 'Wood look', 'en' => 'Wood look', 'ru' => 'Под дерево'],
                    ],
                ],
                [
                    'slug' => 'aluminum',
                    'title' => ['hy' => 'Aluminum profile', 'en' => 'Aluminum profile', 'ru' => 'Алюминиевый профиль'],
                    'children' => [
                        ['hy' => 'Corner', 'en' => 'Corner', 'ru' => 'Угловой'],
                        ['hy' => 'Light line', 'en' => 'Light line', 'ru' => 'Световой'],
                        ['hy' => 'Mounting', 'en' => 'Mounting', 'ru' => 'Монтажный'],
                    ],
                ],
                [
                    'slug' => 'lighting',
                    'title' => ['hy' => 'Lighting', 'en' => 'Lighting', 'ru' => 'Освещение'],
                    'children' => [
                        ['hy' => 'LED strip', 'en' => 'LED strip', 'ru' => 'LED лента'],
                        ['hy' => 'Lamps', 'en' => 'Lamps', 'ru' => 'Лампы'],
                        ['hy' => 'Accessories', 'en' => 'Accessories', 'ru' => 'Аксессуары'],
                    ],
                ],
            ];

            foreach ($categories as $categoryIndex => $category) {
                $categoryId = DB::table('shop_categories')->insertGetId([
                    'slug' => $category['slug'],
                    'status' => true,
                    'sort_order' => $categoryIndex,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($category['title'] as $code => $title) {
                    if (isset($languageIds[$code])) {
                        DB::table('shop_category_translations')->insert([
                            'shop_category_id' => $categoryId,
                            'language_id' => $languageIds[$code],
                            'title' => $title,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                foreach ($category['children'] as $childIndex => $childTitles) {
                    $childId = DB::table('shop_categories')->insertGetId([
                        'parent_id' => $categoryId,
                        'slug' => $category['slug'] . '-' . ($childIndex + 1),
                        'status' => true,
                        'sort_order' => $childIndex,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    foreach ($childTitles as $code => $title) {
                        if (isset($languageIds[$code])) {
                            DB::table('shop_category_translations')->insert([
                                'shop_category_id' => $childId,
                                'language_id' => $languageIds[$code],
                                'title' => $title,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_category_translations');
        Schema::dropIfExists('shop_categories');
    }
};
