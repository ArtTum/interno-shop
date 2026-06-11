<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_privacy_policy_translations', function (Blueprint $table) {
            $table->longText('content_html')->nullable()->after('checklist_aria');
        });
    }

    public function down(): void
    {
        Schema::table('shop_privacy_policy_translations', function (Blueprint $table) {
            $table->dropColumn('content_html');
        });
    }
};
