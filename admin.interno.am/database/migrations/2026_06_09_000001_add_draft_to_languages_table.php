<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('languages') || Schema::hasColumn('languages', 'draft')) {
            return;
        }

        Schema::table('languages', function (Blueprint $table) {
            $table->boolean('draft')->default(false)->after('status')->index();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('languages') || !Schema::hasColumn('languages', 'draft')) {
            return;
        }

        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn('draft');
        });
    }
};
