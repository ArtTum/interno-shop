<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->index('type');
            $table->index('year');
            $table->index('month');
            $table->index('color');
            $table->index('find_aboutus');
            $table->index('user_id');
            $table->index('konsultacia');
            $table->index('call_date');
            $table->index('next_call_date');
            $table->index('day_surgery');

            $table->index(['year', 'month', 'type', 'user_id'], 'idx_dashboard_filter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            //
        });
    }
};
