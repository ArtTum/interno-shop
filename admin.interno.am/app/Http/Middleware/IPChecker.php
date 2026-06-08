<?php

namespace App\Http\Middleware;

use App\Models\GeneralSetting;
use App\Models\UserGroupIP;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IPChecker
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!GeneralSetting::needIPChecker() || Auth::user()->superadmin || return_real_ip_customer($request) == Auth::user()->ip) return $next($request);
        $userGroupId = Auth::user()->user_group_id;

        $approvedIP = UserGroupIP::select('id')
            ->where('user_group_id', $userGroupId)
            ->where('ip', return_real_ip_customer($request))
            ->exists();

        if (!$approvedIP) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
