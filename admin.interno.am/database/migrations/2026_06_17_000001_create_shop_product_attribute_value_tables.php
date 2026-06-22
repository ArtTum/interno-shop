<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('shop_product_attribute_values')) {
            Schema::create('shop_product_attribute_values', function (Blueprint $table) {
                $table->id();
                $table->string('type', 40)->index();
                $table->string('name');
                $table->string('value')->nullable();
                $table->boolean('status')->default(true)->index();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->timestamps();

                $table->unique(['type', 'name'], 'shop_product_attribute_type_name_unique');
            });
        }

        if (!Schema::hasTable('shop_product_attribute_prices')) {
            Schema::create('shop_product_attribute_prices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shop_product_id');
                $table->foreignId('shop_product_attribute_value_id');
                $table->decimal('price', 12, 2)->default(0);
                $table->timestamps();

                $table->unique(['shop_product_id', 'shop_product_attribute_value_id'], 'shop_product_attribute_price_unique');
                $table->foreign('shop_product_id', 'shop_attr_prices_product_fk')
                    ->references('id')
                    ->on('shop_products')
                    ->cascadeOnDelete();
                $table->foreign('shop_product_attribute_value_id', 'shop_attr_prices_value_fk')
                    ->references('id')
                    ->on('shop_product_attribute_values')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_product_attribute_prices');
        Schema::dropIfExists('shop_product_attribute_values');
    }
};
