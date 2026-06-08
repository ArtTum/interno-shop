<?php

namespace Database\Seeders;

use App\Models\UserGroup;
use App\Models\UserGroupPermission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    const PERMISSIONS = [
        ['title' => 'Recommendations', 'name' => 'recommendations'],
        ['title' => 'Hospitals base', 'name' => 'hospitals_bases'],
        ['title' => 'Incomings', 'name' => 'incomings'],
        ['title' => 'Notes', 'name' => 'notes'],
        ['title' => 'SMS base', 'name' => 'sms_bazas'],
        ['title' => 'Clinics', 'name' => 'clinics'],
        ['title' => 'Extended prices', 'name' => 'extended_prices'],
        ['title' => 'Doctors final', 'name' => 'doctors_finals'],
        ['title' => 'SMS templates', 'name' => 'sms_shablons'],
        ['title' => 'Sent SMS history', 'name' => 'sms_histories'],
        ['title' => 'Diseases', 'name' => 'diseases'],
        ['title' => 'Hospitals', 'name' => 'hospitals'],

        ['title' => 'Users menu', 'name' => 'users_menu_link'],
        ['title' => 'Users', 'name' => 'users'],
        ['title' => 'User groups', 'name' => 'users_groups'],
        ['title' => 'Permissions', 'name' => 'permissions'],

        ['title' => 'Outgoings', 'name' => 'outgoings'],
        ['title' => 'Subscribes', 'name' => 'subscribes'],
        ['title' => 'Trash', 'name' => 'trash'],
    ];


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
