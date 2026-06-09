<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('socials')) {
            Schema::create('socials', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('url')->nullable();
                $table->string('icon')->unique();
                $table->string('color', 20)->nullable();
                $table->boolean('status')->default(true)->index();
                $table->boolean('show_in_frontend')->default(false)->index();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->timestamps();
            });
        } else {
            Schema::table('socials', function (Blueprint $table) {
                if (!Schema::hasColumn('socials', 'name')) {
                    $table->string('name')->nullable();
                }

                if (!Schema::hasColumn('socials', 'url')) {
                    $table->string('url')->nullable();
                }

                if (!Schema::hasColumn('socials', 'icon')) {
                    $table->string('icon')->nullable()->unique();
                }

                if (!Schema::hasColumn('socials', 'color')) {
                    $table->string('color', 20)->nullable();
                }

                if (!Schema::hasColumn('socials', 'status')) {
                    $table->boolean('status')->default(true)->index();
                }

                if (!Schema::hasColumn('socials', 'show_in_frontend')) {
                    $table->boolean('show_in_frontend')->default(false)->index();
                }

                if (!Schema::hasColumn('socials', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0)->index();
                }
            });
        }

        if (!Schema::hasTable('social_translations')) {
            Schema::create('social_translations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('social_id')->constrained('socials')->cascadeOnDelete();
                $table->unsignedInteger('language_id');
                $table->string('url')->nullable();
                $table->timestamps();

                $table->unique(['social_id', 'language_id'], 'social_language_unique');
                $table->foreign('language_id', 'social_language_fk')->references('id')->on('languages')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('social_translations');
        Schema::dropIfExists('socials');
    }
};
