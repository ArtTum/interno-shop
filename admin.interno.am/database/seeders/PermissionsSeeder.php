<?php

namespace Database\Seeders;

use App\Models\UserGroup;
use App\Models\UserGroupPermission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    const PERMISSIONS = [
        ['title' => 'Settings menu', 'name' => 'settings_menu_link'],
        ['title' => 'Languages', 'name' => 'languages'],
        ['title' => 'Medias', 'name' => 'medias'],
        ['title' => 'Shop categories', 'name' => 'shop_categories'],
        ['title' => 'Shop products', 'name' => 'shop_products'],
        ['title' => 'Shop product types', 'name' => 'shop_product_option_types'],
        ['title' => 'Shop product colors', 'name' => 'shop_product_colors'],
        ['title' => 'Users menu', 'name' => 'users_menu_link'],
        ['title' => 'Users', 'name' => 'users'],
        ['title' => 'User groups', 'name' => 'users_groups'],
        ['title' => 'Permissions', 'name' => 'permissions'],    ];


    public function run(): void
    {
        $userGroupsIds = UserGroup::select('id')->pluck('id')->toArray();

        foreach ($userGroupsIds as $id) {
            foreach (self::PERMISSIONS as $permission) {
                if (UserGroupPermission::where('user_group_id', $id)->where('name', $permission['name'])->exists()) continue;

                $value = $id === 1 ? true : false;
                UserGroupPermission::create(
                    [
                        'user_group_id' => $id,
                        'title' => $permission['title'],
                        'name' => $permission['name'],
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
}
