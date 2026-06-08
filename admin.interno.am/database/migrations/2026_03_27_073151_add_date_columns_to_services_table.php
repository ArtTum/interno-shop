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
            $table->date('call_date_new')->nullable()->after('call_date');
            $table->date('next_call_date_new')->nullable()->after('next_call_date');
            $table->date('finish_date_new')->nullable()->after('finish_date');
            $table->date('day_surgery_new')->nullable()->after('day_surgery');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['call_date_new', 'next_call_date_new', 'finish_date_new', 'day_surgery_new']);
        });
    }
};
