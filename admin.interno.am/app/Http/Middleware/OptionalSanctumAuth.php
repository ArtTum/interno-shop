<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OptionalSanctumAuth
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->bearerToken()) {
            $user = auth('sanctum')->user();
            if ($user) {
                auth()->setUser($user);
            }
        }

        return $next($request);
    }
}
