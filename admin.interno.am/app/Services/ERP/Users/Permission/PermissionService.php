<?php

namespace App\Services\ERP\Users\Permission;

use App\Constants\PermissionConstants;
use App\Repositories\UserGroup\UserGroupRepository;
use App\Repositories\UserGroupPermission\UserGroupPermissionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PermissionService
{
    public function __construct(
        UserGroupPermissionRepository $repository,
        UserGroupRepository           $userGroupRepository,
    )
    {
        $this->repository = $repository;
        $this->userGroupRepository = $userGroupRepository;
    }

    public function fetch(array $data): JsonResponse
    {
        $select = "id, title, can_view, can_edit, can_delete, can_export, can_upload, can_add, updated_at, false as changed, name";
        $userGroups = $this->userGroupRepository->fetch("id as value, name as label", [], [], [], [], []);

        $ordering = [
            'field' => 'name',
            'direction' => 'asc'
        ];

        $response = $this->repository->fetch($select, [], $ordering, $data, [], []);
        $preparedInfo = [];

        foreach (PermissionConstants::ORDERING as $key => $childs) {

            if (!isset($response[$key])) continue;

            $permission = $response[$key];
            $permission->is_parent = true;
            $preparedInfo[] = $permission;

            foreach ($childs as $child) {
                if (!isset($response[$child])) continue;

                $permission = $response[$child];
                $permissionName = "- " . $permission->title;
                unset($permission->title);
                $permission->title = $permissionName;
                $preparedInfo[] = $permission;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully reached!',
            'data' => $preparedInfo,
            'userGroups' => $userGroups
        ]);
    }

    public function update(array $data): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $data['params'];

            foreach ($data as $item) {
                if (!$item['changed']) continue;

                $this->repository->update('id', $item['id'], [
                    'can_view' => $item['can_view'],
                    'can_edit' => $item['can_edit'],
                    'can_delete' => $item['can_delete'],
                    'can_export' => $item['can_export'],
                    'can_upload' => $item['can_upload'],
                    'can_add' => $item['can_add'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Successfully updated!'
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception);
        }
    }
}
