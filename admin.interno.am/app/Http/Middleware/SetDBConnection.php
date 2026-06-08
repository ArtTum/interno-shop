<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetDBConnection
{
    public function handle(Request $request, Closure $next): Response
    {
        $vendorKey = $request->header('VendorKey');

        if (!$vendorKey) {
            $vendorKey = $request->input('vendor_key');
        }
        $res = setDBConnection($vendorKey);

        if ($res === true) {
            return $next($request);
        }

        return $res;
    }
}
