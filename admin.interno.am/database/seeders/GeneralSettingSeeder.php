<?php

namespace Database\Seeders;

use App\Constants\GeneralSettingConstants;
use App\Models\GeneralSetting;
use Illuminate\Database\Seeder;

class GeneralSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (GeneralSettingConstants::GENERAL_SETTINGS_KEY as $key => $group) {
            if ($key === 'prices_include_taxes') {
                GeneralSetting::firstOrCreate(
                    [
                        'key' => $key,
                    ],
                    [
                        'value' => 'yes',
                        'group' => $group,
                    ]
                );
            } else if ($key === 'use_ip_checker') {
                GeneralSetting::firstOrCreate(
                    [
                        'key' => $key,
                    ],
                    [
                        'value' => 'no',
                        'group' => $group,
                    ]
                );
            } else if ($key === 'per_package_cost') {
                GeneralSetting::firstOrCreate(
                    [
                        'key' => $key,
                    ],
                    [
                        'value' => 1.3,
                        'group' => $group,
                    ]
                );
            } else if ($key === 'delivery_working_days') {
                GeneralSetting::firstOrCreate(
                    [
                        'key' => $key,
                    ],
                    [
                        'value' => 3,
                        'group' => $group,
                    ]
                );
            } else if ($key === 'trustpilot') {
                GeneralSetting::firstOrCreate(
                    [
                        'key' => $key,
                    ],
                    [
                        'is_visible' => false,
                        'group' => $group,
                    ]
                );
            }  else if ($key === 'menu_styles') {
                GeneralSetting::firstOrCreate(
                    [
                        'key' => $key,
                    ],
                    [
                        'is_visible' => false,
                        'group' => $group,
                    ]
                );
            } else if ($key === 'paypal_mode') {
                GeneralSetting::firstOrCreate(
                    [
                        'key' => $key,
                    ],
                    [
                        'value' => 'live',
                        'group' => $group,
                    ]
                );
            }  else if ($key === 'stripe_mode') {
                GeneralSetting::firstOrCreate(
                    [
                        'key' => $key,
                    ],
                    [
                        'value' => 'live',
                        'group' => $group,
                    ]
                );
            } else {
                GeneralSetting::firstOrCreate(
                    [
                        'key' => $key
                    ],
                    [
                        'group' => $group
                    ]
                );
            }
        }
    }
}
