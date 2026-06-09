<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_seo_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_key', 80);
            $table->unsignedInteger('language_id');
            $table->string('title')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->boolean('status')->default(true)->index();
            $table->timestamps();

            $table->unique(['page_key', 'language_id'], 'shop_seo_page_language_unique');
            $table->index('page_key');
            $table->foreign('language_id', 'shop_seo_language_fk')->references('id')->on('languages')->cascadeOnDelete();
        });

        $languageIds = DB::table('languages')->pluck('id', 'code');
        $defaults = [
            'home' => 'Home',
            'contact' => 'Contact',
            'cart' => 'Cart',
            'checkout-success' => 'Checkout success',
            'search' => 'Search',
            'privacy-policy' => 'Privacy Policy',
            'category' => 'Category',
            'product' => 'Product',
        ];

        foreach ($defaults as $pageKey => $title) {
            foreach ($languageIds as $languageId) {
                DB::table('shop_seo_pages')->insert([
                    'page_key' => $pageKey,
                    'language_id' => $languageId,
                    'title' => $title,
                    'meta_title' => $title,
                    'meta_description' => null,
                    'meta_keywords' => null,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_seo_pages');
    }
};
