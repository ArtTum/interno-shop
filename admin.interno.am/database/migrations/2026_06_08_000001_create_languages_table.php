<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('currency_id')->nullable()->index();
            $table->string('code', 10)->unique();
            $table->string('name', 80);
            $table->boolean('status')->default(true)->index();
            $table->boolean('base')->default(false)->index();
            $table->string('local_for_trustpilot', 10)->nullable();
            $table->string('hreflang', 10)->nullable();
            $table->boolean('default_hreflang')->default(false)->index();
            $table->string('email')->nullable();
            $table->text('microsoft_access_token')->nullable();
            $table->text('microsoft_refresh_token')->nullable();
            $table->timestamp('microsoft_token_expires_at')->nullable();
            $table->string('oauth_state')->nullable();
            $table->boolean('is_rtl')->default(false);
            $table->timestamps();
        });

        DB::table('languages')->insert([
            [
                'code' => 'hy',
                'name' => 'Հայերեն',
                'status' => true,
                'base' => true,
                'hreflang' => 'hy',
                'local_for_trustpilot' => 'hy',
                'default_hreflang' => true,
                'is_rtl' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'en',
                'name' => 'English',
                'status' => true,
                'base' => false,
                'hreflang' => 'en',
                'local_for_trustpilot' => 'en',
                'default_hreflang' => false,
                'is_rtl' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ru',
                'name' => 'Русский',
                'status' => true,
                'base' => false,
                'hreflang' => 'ru',
                'local_for_trustpilot' => 'ru',
                'default_hreflang' => false,
                'is_rtl' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
