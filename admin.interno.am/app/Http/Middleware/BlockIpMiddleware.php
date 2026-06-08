<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BlockIpMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $blockedIps = ['168.119.245.229'];
        $clientIp = return_real_ip_customer($request);

        if (in_array($clientIp, $blockedIps)) {
            Log::channel('ip-blocking-info')->info("Blocking IP address: {$clientIp}");

            return response()->json([
                'message' => 'Forbidden'
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
