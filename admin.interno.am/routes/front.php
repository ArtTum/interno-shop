<?php

use App\Http\Controllers\API\ERP\ShopFrontendController;
use Illuminate\Support\Facades\Route;

Route::prefix('front')->group(function () {
    $corsHeaders = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Accept, Authorization, X-Requested-With',
        'Access-Control-Max-Age' => '86400',
    ];

    Route::options('{any}', fn () => response('', 204, $corsHeaders))->where('any', '.*');

    Route::get('shop', [ShopFrontendController::class, 'publicConfig']);
    Route::post('orders', [ShopFrontendController::class, 'storeOrder']);
});
