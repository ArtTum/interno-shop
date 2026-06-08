<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $userId = DB::table('users')->where('email', 'alina.nordikyan@doctor911.am')->value('id');

        if ($userId) {
            DB::table('services')->whereNull('user_id')->update(['user_id' => $userId]);
        }
    }

    public function down(): void
    {
        //
    }
};
