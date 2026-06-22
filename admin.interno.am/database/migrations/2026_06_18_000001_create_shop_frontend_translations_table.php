<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('shop_frontend_translations')) {
            Schema::create('shop_frontend_translations', function (Blueprint $table) {
                $table->id();
                $table->string('key');
                $table->unsignedBigInteger('language_id')->index();
                $table->text('value')->nullable();
                $table->timestamps();
                $table->unique(['key', 'language_id']);
            });
        }

        $this->seedFromCurrentConfig();
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_frontend_translations');
    }

    private function seedFromCurrentConfig(): void
    {
        if (!Schema::hasTable('languages') || !Schema::hasTable('shop_frontend_translations')) {
            return;
        }

        $payload = $this->currentConfig();
        $translations = $payload['translations'] ?? [];

        if (!is_array($translations) || empty($translations)) {
            return;
        }

        $languages = DB::table('languages')->pluck('id', 'code');
        $now = now();

        foreach ($translations as $languageCode => $items) {
            $languageId = $languages[$languageCode] ?? null;

            if (!$languageId || !is_array($items)) {
                continue;
            }

            foreach ($items as $key => $value) {
                DB::table('shop_frontend_translations')->updateOrInsert(
                    [
                        'key' => $key,
                        'language_id' => $languageId,
                    ],
                    [
                        'value' => is_scalar($value) ? (string) $value : '',
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }
    }

    private function currentConfig(): array
    {
        $path = storage_path('app/shop_frontend.json');

        if (File::exists($path)) {
            $decoded = json_decode(File::get($path), true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return config('shop_frontend', []);
    }
};
