<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $permissions = [
        ['title' => 'Settings menu', 'name' => 'settings_menu_link'],
        ['title' => 'Languages', 'name' => 'languages'],
    ];

    public function up(): void
    {
        if (!Schema::hasTable('user_groups') || !Schema::hasTable('user_group_permissions')) {
            return;
        }

        $now = now();
        $userGroupIds = DB::table('user_groups')->pluck('id');

        foreach ($userGroupIds as $userGroupId) {
            foreach ($this->permissions as $permission) {
                $exists = DB::table('user_group_permissions')
                    ->where('user_group_id', $userGroupId)
                    ->where('name', $permission['name'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                $enabled = (int) $userGroupId === 1;

                DB::table('user_group_permissions')->insert([
                    'user_group_id' => $userGroupId,
                    'title' => $permission['title'],
                    'name' => $permission['name'],
                    'can_view' => $enabled,
                    'can_add' => $enabled,
                    'can_edit' => $enabled,
                    'can_delete' => $enabled,
                    'can_upload' => $enabled,
                    'can_export' => $enabled,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('user_group_permissions')) {
            return;
        }

        DB::table('user_group_permissions')
            ->whereIn('name', array_column($this->permissions, 'name'))
            ->delete();
    }
};
