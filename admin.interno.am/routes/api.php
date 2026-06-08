<?php

use App\Http\Middleware\SetTenantDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::post('dropzone-checker', function () {
    return response()->json([
        'success' => true,
        'message' => 'Successfully created!'
    ]);
});
Route::middleware([SetTenantDatabase::class, 'optional.sanctum'])
    ->group(function () {
        Route::post('/custom-broadcasting/auth', function (Request $request) {
            return Broadcast::auth($request);
        });
    });
require __DIR__ . '/erp.php';

