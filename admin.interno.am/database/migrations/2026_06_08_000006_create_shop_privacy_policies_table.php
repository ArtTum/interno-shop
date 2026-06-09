<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_privacy_policies', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->default('privacy-policy');
            $table->string('updated_at_label')->nullable();
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('shop_privacy_policy_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_privacy_policy_id');
            $table->unsignedInteger('language_id');
            $table->string('kicker')->nullable();
            $table->string('title')->nullable();
            $table->text('intro')->nullable();
            $table->string('badge_title')->nullable();
            $table->text('badge_text')->nullable();
            $table->string('summary_label')->nullable();
            $table->string('summary_title')->nullable();
            $table->text('summary_text')->nullable();
            $table->string('updated_label')->nullable();
            $table->string('summary_aria')->nullable();
            $table->string('checklist_aria')->nullable();
            $table->timestamps();

            $table->unique(['shop_privacy_policy_id', 'language_id'], 'privacy_policy_language_unique');
            $table->foreign('shop_privacy_policy_id', 'privacy_translation_policy_fk')->references('id')->on('shop_privacy_policies')->cascadeOnDelete();
            $table->foreign('language_id', 'privacy_translation_language_fk')->references('id')->on('languages')->cascadeOnDelete();
        });

        Schema::create('shop_privacy_policy_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_privacy_policy_id');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();

            $table->foreign('shop_privacy_policy_id', 'privacy_checklist_policy_fk')->references('id')->on('shop_privacy_policies')->cascadeOnDelete();
        });

        Schema::create('shop_privacy_policy_checklist_item_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_privacy_policy_checklist_item_id');
            $table->unsignedInteger('language_id');
            $table->text('text')->nullable();
            $table->timestamps();

            $table->unique(['shop_privacy_policy_checklist_item_id', 'language_id'], 'privacy_checklist_language_unique');
            $table->foreign('shop_privacy_policy_checklist_item_id', 'privacy_checklist_item_fk')->references('id')->on('shop_privacy_policy_checklist_items')->cascadeOnDelete();
            $table->foreign('language_id', 'privacy_checklist_language_fk')->references('id')->on('languages')->cascadeOnDelete();
        });

        Schema::create('shop_privacy_policy_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_privacy_policy_id');
            $table->string('section_index', 10)->nullable();
            $table->string('icon', 20)->nullable();
            $table->boolean('is_wide')->default(false);
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();

            $table->foreign('shop_privacy_policy_id', 'privacy_section_policy_fk')->references('id')->on('shop_privacy_policies')->cascadeOnDelete();
        });

        Schema::create('shop_privacy_policy_section_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_privacy_policy_section_id');
            $table->unsignedInteger('language_id');
            $table->string('title')->nullable();
            $table->text('text')->nullable();
            $table->timestamps();

            $table->unique(['shop_privacy_policy_section_id', 'language_id'], 'privacy_section_language_unique');
            $table->foreign('shop_privacy_policy_section_id', 'privacy_section_item_fk')->references('id')->on('shop_privacy_policy_sections')->cascadeOnDelete();
            $table->foreign('language_id', 'privacy_section_language_fk')->references('id')->on('languages')->cascadeOnDelete();
        });

        $this->seedDefaultPrivacyPolicy();
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_privacy_policy_section_translations');
        Schema::dropIfExists('shop_privacy_policy_sections');
        Schema::dropIfExists('shop_privacy_policy_checklist_item_translations');
        Schema::dropIfExists('shop_privacy_policy_checklist_items');
        Schema::dropIfExists('shop_privacy_policy_translations');
        Schema::dropIfExists('shop_privacy_policies');
    }

    private function seedDefaultPrivacyPolicy(): void
    {
        $privacy = config('shop_frontend.privacy', []);
        $languageIds = DB::table('languages')->pluck('id', 'code');

        if ($languageIds->isEmpty()) {
            return;
        }

        $policyId = DB::table('shop_privacy_policies')->insertGetId([
            'slug' => 'privacy-policy',
            'updated_at_label' => $privacy['updatedAt'] ?? '03.06.2026',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (($privacy['content'] ?? []) as $code => $content) {
            if (!isset($languageIds[$code])) {
                continue;
            }

            DB::table('shop_privacy_policy_translations')->insert([
                'shop_privacy_policy_id' => $policyId,
                'language_id' => $languageIds[$code],
                'kicker' => $content['kicker'] ?? null,
                'title' => $content['title'] ?? null,
                'intro' => $content['intro'] ?? null,
                'badge_title' => $content['badgeTitle'] ?? null,
                'badge_text' => $content['badgeText'] ?? null,
                'summary_label' => $content['summaryLabel'] ?? null,
                'summary_title' => $content['summaryTitle'] ?? null,
                'summary_text' => $content['summaryText'] ?? null,
                'updated_label' => $content['updated'] ?? null,
                'summary_aria' => $content['summaryAria'] ?? null,
                'checklist_aria' => $content['checklistAria'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $privacyContent = $privacy['content'] ?? [];
        $baseContent = $privacyContent['hy'] ?? (is_array($privacyContent) ? reset($privacyContent) : []);
        $baseContent = is_array($baseContent) ? $baseContent : [];

        foreach (($baseContent['checklist'] ?? []) as $itemIndex => $unused) {
            $itemId = DB::table('shop_privacy_policy_checklist_items')->insertGetId([
                'shop_privacy_policy_id' => $policyId,
                'sort_order' => $itemIndex,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach (($privacy['content'] ?? []) as $code => $content) {
                if (!isset($languageIds[$code])) {
                    continue;
                }

                DB::table('shop_privacy_policy_checklist_item_translations')->insert([
                    'shop_privacy_policy_checklist_item_id' => $itemId,
                    'language_id' => $languageIds[$code],
                    'text' => $content['checklist'][$itemIndex] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach (($baseContent['sections'] ?? []) as $sectionIndex => $section) {
            $sectionId = DB::table('shop_privacy_policy_sections')->insertGetId([
                'shop_privacy_policy_id' => $policyId,
                'section_index' => $section['index'] ?? str_pad((string)($sectionIndex + 1), 2, '0', STR_PAD_LEFT),
                'icon' => $section['icon'] ?? null,
                'is_wide' => ($section['index'] ?? null) === '05',
                'sort_order' => $sectionIndex,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach (($privacy['content'] ?? []) as $code => $content) {
                if (!isset($languageIds[$code])) {
                    continue;
                }

                DB::table('shop_privacy_policy_section_translations')->insert([
                    'shop_privacy_policy_section_id' => $sectionId,
                    'language_id' => $languageIds[$code],
                    'title' => $content['sections'][$sectionIndex]['title'] ?? null,
                    'text' => $content['sections'][$sectionIndex]['text'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
