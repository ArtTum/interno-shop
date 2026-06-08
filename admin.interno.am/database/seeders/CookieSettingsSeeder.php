<?php

namespace Database\Seeders;

use App\Models\CookieSetting;
use Illuminate\Database\Seeder;

class CookieSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'modal_title', 'label' => 'Modal title'],
            ['key' => 'modal_description', 'label' => 'Modal description'],
            ['key' => 'privacy_policy_link', 'label' => 'Privacy Policy link'],
            ['key' => 'privacy_policy_link_label', 'label' => 'Privacy Policy link label'],
            ['key' => 'accept_button_text', 'label' => 'Accept button text'],
            ['key' => 'reject_button_text', 'label' => 'Reject button text'],
            ['key' => 'settings_button_text', 'label' => 'Settings button text'],
            ['key' => 'settings_modal_title', 'label' => 'Settings modal title'],
            ['key' => 'settings_modal_accept_all_button_text', 'label' => 'Settings modal accept all button text'],
            ['key' => 'settings_modal_reject_all_button_text', 'label' => 'Settings modal reject all button text'],
            ['key' => 'settings_modal_save_button_text', 'label' => 'Settings modal save button text'],
        ];

        foreach ($settings as $setting) {
            CookieSetting::updateOrCreate(
                [
                    'key' => $setting['key']
                ],
                $setting
            );
        }
    }
}
