<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            $table->foreignId('media_id')->nullable()->after('kind')->constrained('media')->nullOnDelete();
            $table->json('gallery_media_ids')->nullable()->after('gallery');
        });
    }

    public function down(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('media_id');
            $table->dropColumn('gallery_media_ids');
        });
    }
};
