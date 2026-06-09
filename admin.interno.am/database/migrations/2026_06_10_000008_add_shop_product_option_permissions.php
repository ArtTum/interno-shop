<?php

use App\Models\UserGroup;
use App\Models\UserGroupPermission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (UserGroup::query()->pluck('id') as $userGroupId) {
            $value = (int) $userGroupId === 1;

            foreach ($this->permissions() as $permission) {
                UserGroupPermission::query()->firstOrCreate(
                    [
                        'user_group_id' => $userGroupId,
                        'name' => $permission['name'],
                    ],
                    [
                        'title' => $permission['title'],
                        'can_view' => $value,
                        'can_edit' => $value,
                        'can_add' => $value,
                        'can_delete' => $value,
                        'can_upload' => $value,
                        'can_export' => $value,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        UserGroupPermission::query()
            ->whereIn('name', collect($this->permissions())->pluck('name')->all())
            ->delete();
    }

    private function permissions(): array
    {
        return [
            ['title' => 'Shop product types', 'name' => 'shop_product_option_types'],
            ['title' => 'Shop product colors', 'name' => 'shop_product_colors'],
        ];
    }
};
