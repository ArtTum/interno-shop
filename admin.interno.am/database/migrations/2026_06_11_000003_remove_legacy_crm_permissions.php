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

//        DB::table('user_group_permissions')
//            ->whereIn('name', [
//                'hospitals_bases',
//                'incomings',
//                'sms_bazas',
//                'clinics',
//                'extended_prices',
//                'doctors_finals',
//                'sms_shablons',
//                'sms_histories',
//                'diseases',
//                'hospitals',
//                'outgoings',
//                'subscribes',
//                'notes',
//                'trash',
//            ])
//            ->delete();
    }

    public function down(): void
    {
        //
    }
};
