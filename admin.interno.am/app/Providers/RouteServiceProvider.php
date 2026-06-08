<?php

namespace App\Providers;

use App\Http\Middleware\BlockIpMiddleware;
use App\Http\Middleware\CheckClientCertificate;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\IPChecker;
use App\Http\Middleware\OnlyForSuperadmin;
use App\Http\Middleware\OptionalSanctumAuth;
use App\Http\Middleware\SetDBConnection;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app['router']->aliasMiddleware('check.permission', CheckPermission::class);
        $this->app['router']->aliasMiddleware('only.for.superadmin', OnlyForSuperadmin::class);
        $this->app['router']->aliasMiddleware('set.db.connection', SetDBConnection::class);
        $this->app['router']->aliasMiddleware('ip.checker', IPChecker::class);
        $this->app['router']->aliasMiddleware('optional.sanctum', OptionalSanctumAuth::class);
        $this->app['router']->aliasMiddleware('block.ip.middleware', BlockIpMiddleware::class);
        $this->app['router']->aliasMiddleware('check.client.certificate', CheckClientCertificate::class);
    }
}
