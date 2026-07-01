<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('shop_product_related_products')) {
            return;
        }

        Schema::create('shop_product_related_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->foreignId('related_shop_product_id')->constrained('shop_products')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();

            $table->unique(['shop_product_id', 'related_shop_product_id'], 'shop_product_related_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_product_related_products');
    }
};
