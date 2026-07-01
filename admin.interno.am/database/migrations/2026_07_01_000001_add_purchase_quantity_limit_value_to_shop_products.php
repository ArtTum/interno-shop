<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('shop_products', 'purchase_quantity_limit')) {
            return;
        }

        Schema::table('shop_products', function (Blueprint $table) {
            $column = $table->unsignedInteger('purchase_quantity_limit')->nullable();

            if (Schema::hasColumn('shop_products', 'purchase_quantity_limited')) {
                $column->after('purchase_quantity_limited');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('shop_products', 'purchase_quantity_limit')) {
            return;
        }

        Schema::table('shop_products', function (Blueprint $table) {
            $table->dropColumn('purchase_quantity_limit');
        });
    }
};
