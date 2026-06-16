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

            UserGroupPermission::query()->firstOrCreate(
                [
                    'user_group_id' => $userGroupId,
                    'name' => 'shop_product_attribute_values',
                ],
                [
                    'title' => 'Shop product attribute values',
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

    public function down(): void
    {
        UserGroupPermission::query()
            ->where('name', 'shop_product_attribute_values')
            ->delete();
    }
};
