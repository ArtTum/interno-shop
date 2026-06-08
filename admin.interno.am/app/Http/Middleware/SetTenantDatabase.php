<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SetTenantDatabase
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!empty(request()->headers->all()['x-vendor-id'][0])) {
            $vendorKey = request()->headers->all()['x-vendor-id'][0];
            $res = setDBConnection($vendorKey);
        }

        return $next($request);
    }
}
