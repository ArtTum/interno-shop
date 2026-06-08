<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Covers: WHERE year=? AND month=? AND type IN(...) ORDER BY id DESC
            // idx_dashboard_filter (year, month, type, user_id) cannot help with ORDER BY id
            $table->index(['year', 'month', 'type', 'id'], 'idx_services_hospitals_base');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('idx_services_hospitals_base');
        });
    }
};