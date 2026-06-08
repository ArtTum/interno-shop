<?php

use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('site/add-subscribes', [SiteController::class, 'addSubscribes']);

Route::get('{any?}', fn () => view('app'))->where('any', '.*');
