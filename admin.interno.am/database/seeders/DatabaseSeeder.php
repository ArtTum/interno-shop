<?php

namespace Database\Seeders;

use App\Constants\GeneralSettingConstants;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserGroupsSeeder::class);
        $this->call(LanguageSeeder::class);
        $this->call(CurrencySeeder::class);
        $this->call(SocialsSeeder::class);
        $this->call(MediaSettingSeeder::class);
        $this->call(DocumentSettingSeeder::class);
        $this->call(PermissionsSeeder::class);
        $this->call(GeneralSettingSeeder::class);
        $this->call(CookieSettingsSeeder::class);
    }
}
