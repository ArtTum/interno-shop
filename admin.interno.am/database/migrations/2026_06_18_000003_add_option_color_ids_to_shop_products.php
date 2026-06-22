<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            $table->json('option_color_ids')->nullable()->after('option_color_id');
        });

        DB::table('shop_products')
            ->whereNotNull('option_color_id')
            ->orderBy('id')
            ->get(['id', 'option_color_id'])
            ->each(function ($product) {
                DB::table('shop_products')
                    ->where('id', $product->id)
                    ->update([
                        'option_color_ids' => json_encode([(int) $product->option_color_id]),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            $table->dropColumn('option_color_ids');
        });
    }
};
