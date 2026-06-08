<?php

namespace App\Http\Middleware;

use App\Models\UserGroupPermission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permissionName, string $permissionTypes): Response
    {
        $permissionTypes = explode('|', $permissionTypes);
        $userGroupId = Auth::user()->user_group_id;

        $permissionExists = UserGroupPermission::select('id')
            ->where('user_group_id', $userGroupId)
            ->where('name', $permissionName)
            ->when(count($permissionTypes) > 1, function ($query) use ($permissionTypes) {
                $query->where(function ($query) use ($permissionTypes) {
                    foreach ($permissionTypes as $permissionType) {
                        $query->orWhere($permissionType, true);
                    }
                });
            })
            ->when(count($permissionTypes) === 1, function ($query) use ($permissionTypes) {
                $query->orWhere($permissionTypes[0], true);
            })
            ->exists();

        if ($permissionName === 'users' && Auth::user()->id === $request->id) {
            $permissionExists = true;
        }

        if (!$permissionExists) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
