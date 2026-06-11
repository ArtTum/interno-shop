<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_group_permissions')) {
            return;
        }

        DB::table('user_group_permissions')
            ->where('name', 'recommendations')
            ->delete();
    }

    public function down(): void
    {
        //
    }
};
