<?php

use App\Http\Controllers\API\ERP\ShopFrontendController;
use Illuminate\Support\Facades\Route;

Route::prefix('front')->group(function () {
    Route::get('shop', [ShopFrontendController::class, 'publicConfig']);
    Route::post('orders', [ShopFrontendController::class, 'storeOrder']);
});
