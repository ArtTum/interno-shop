<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_orders', function (Blueprint $table) {
            $table->id();
            $table->string('status', 40)->default('new')->index();
            $table->string('language', 10)->default('hy');

            // Customer
            $table->string('customer_first_name', 120);
            $table->string('customer_last_name', 120)->nullable();
            $table->string('customer_phone', 80);
            $table->string('customer_email', 160);
            $table->string('customer_address', 500)->nullable();
            $table->string('customer_master_code', 120)->nullable();

            // Craftsman snapshot
            $table->unsignedBigInteger('craftsman_id')->nullable()->index();
            $table->string('craftsman_code', 80)->nullable();
            $table->string('craftsman_name', 240)->nullable();

            // Items + total
            $table->json('items');
            $table->decimal('total', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_orders');
    }
};
