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
            $table->index('hospital_id');
            $table->index('disease_id');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['hospital_id']);
            $table->dropIndex(['disease_id']);
            $table->dropIndex(['deleted_at']);
        });
    }
};
