<?php

namespace Database\Seeders;

use App\Models\UserGroup;
use Illuminate\Database\Seeder;

class UserGroupsSeeder extends Seeder
{
    const ITEMS = [
        'Admin', 'CEO', 'Employee', 'Marketing', 'Management', 'Accounting', 'Customer'
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::ITEMS as $item) {
            UserGroup::updateOrCreate(
                ['name' => $item]
            );
        }
    }
}
