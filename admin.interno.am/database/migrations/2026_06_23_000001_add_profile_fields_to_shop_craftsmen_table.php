<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_craftsmen', function (Blueprint $table) {
            if (!Schema::hasColumn('shop_craftsmen', 'media_id')) {
                $table->foreignId('media_id')->nullable()->after('id')->constrained('media')->nullOnDelete();
            }

            if (!Schema::hasColumn('shop_craftsmen', 'work_region')) {
                $table->string('work_region')->nullable()->after('phone')->index();
            }

            if (!Schema::hasColumn('shop_craftsmen', 'work_city')) {
                $table->string('work_city')->nullable()->after('work_region')->index();
            }

            if (!Schema::hasColumn('shop_craftsmen', 'work_field')) {
                $table->string('work_field')->nullable()->after('work_city')->index();
            }

            if (!Schema::hasColumn('shop_craftsmen', 'has_whatsapp')) {
                $table->boolean('has_whatsapp')->default(false)->after('work_field');
            }

            if (!Schema::hasColumn('shop_craftsmen', 'has_viber')) {
                $table->boolean('has_viber')->default(false)->after('has_whatsapp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shop_craftsmen', function (Blueprint $table) {
            if (Schema::hasColumn('shop_craftsmen', 'media_id')) {
                $table->dropConstrainedForeignId('media_id');
            }

            foreach (['work_region', 'work_city', 'work_field', 'has_whatsapp', 'has_viber'] as $column) {
                if (Schema::hasColumn('shop_craftsmen', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
