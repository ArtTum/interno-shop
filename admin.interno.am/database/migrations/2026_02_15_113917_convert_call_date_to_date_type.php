<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
//        // 1️⃣ Ավելացնում ենք նոր DATE column
//        Schema::table('services', function (Blueprint $table) {
//            $table->date('call_date_new')->nullable()->after('call_date');
//        });
//
//        // 2️⃣ Convert ենք անում տվյալները d.m.Y → Y-m-d
//        DB::statement("
//            UPDATE services
//            SET call_date_new = STR_TO_DATE(call_date, '%d.%m.%Y')
//            WHERE call_date IS NOT NULL
//        ");
//
//        // 3️⃣ Ջնջում ենք հինը
//        Schema::table('services', function (Blueprint $table) {
//            $table->dropColumn('call_date');
//        });
//
//        // 4️⃣ Rename անում ենք նոր column-ը
//        Schema::table('services', function (Blueprint $table) {
//            $table->renameColumn('call_date_new', 'call_date');
//        });
//
//        // 5️⃣ Index
//        Schema::table('services', function (Blueprint $table) {
//            $table->index('call_date');
//        });
    }

    public function down(): void
    {

    }
};
