<?php

use App\Http\Controllers\API\ERP\AllCountryController;
use App\Http\Controllers\API\ERP\AuthController;
use App\Http\Controllers\API\ERP\CookieSettingController;
use App\Http\Controllers\API\ERP\CustomerGroupController;
use App\Http\Controllers\API\ERP\DashboardController;
use App\Http\Controllers\API\ERP\GeneralController;
use App\Http\Controllers\API\ERP\GeneralSettingController;
use App\Http\Controllers\API\ERP\LanguageController;
use App\Http\Controllers\API\ERP\MemberGroupController;
use App\Http\Controllers\API\ERP\MediaController;
use App\Http\Controllers\API\ERP\PermalinkController;
use App\Http\Controllers\API\ERP\PermissionController;
use App\Http\Controllers\API\ERP\ProgramController;
use App\Http\Controllers\API\ERP\ShopFrontendController;
use App\Http\Controllers\API\ERP\ShopCategoryController;
use App\Http\Controllers\API\ERP\ShopProductColorController;
use App\Http\Controllers\API\ERP\ShopProductController;
use App\Http\Controllers\API\ERP\ShopProductOptionTypeController;
use App\Http\Controllers\API\ERP\UserController;
use App\Http\Controllers\API\ERP\UserGroupController;
use App\Http\Controllers\API\ERP\VendorController;
use App\Http\Middleware\CheckClientCertificate;
use App\Http\Middleware\IPChecker;
use App\Http\Middleware\SetDBConnection;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

Route::group(['prefix' => 'erp'], function () {
    Route::middleware([IPChecker::class, SetDBConnection::class, EnsureFrontendRequestsAreStateful::class, StartSession::class, CheckClientCertificate::class, 'guest:sanctum'])
        ->group(function () {
            Route::post('login', [AuthController::class, 'login']);
        });

    Route::middleware([IPChecker::class, SetDBConnection::class, EnsureFrontendRequestsAreStateful::class, StartSession::class, CheckClientCertificate::class, 'auth:sanctum'])
        ->group(function () {
            Route::group(['prefix' => 'auth'], function () {
                Route::get('fetch', [AuthController::class, 'fetch']);
                Route::post('logout', [AuthController::class, 'logout']);
                Route::post('switch', [AuthController::class, 'switch']);
            });


            Route::group(['prefix' => 'general-info'], function () {
                Route::get('fetch', [GeneralController::class, 'fetch']);
//                Route::get('fetch-vendors-for-switch', [GeneralController::class, 'fetchVendorsForSwitch']);
            });

            Route::group(['prefix' => 'languages'], function () {
                // get requests
                Route::get('fetch', [LanguageController::class, 'fetch'])->middleware('check.permission:languages,can_view');

                Route::get('fetch-by-field', [LanguageController::class, 'fetchByField'])->middleware('check.permission:languages,can_view');
                // post requests
                Route::post('insert', [LanguageController::class, 'insert'])->middleware('check.permission:languages,can_add');

                Route::put('update', [LanguageController::class, 'update'])->middleware('check.permission:languages,can_edit');

                Route::delete('delete/{id}', [LanguageController::class, 'delete'])->middleware('check.permission:languages,can_delete');
            });

            Route::group(['prefix' => 'shop-frontend'], function () {
                Route::get('fetch', [ShopFrontendController::class, 'fetch'])->middleware('check.permission:languages,can_view');
                Route::put('update', [ShopFrontendController::class, 'update'])->middleware('check.permission:languages,can_edit');
                Route::get('orders', [ShopFrontendController::class, 'orders'])->middleware('check.permission:languages,can_view');
            });

            Route::group(['prefix' => 'shop-categories'], function () {
                Route::get('fetch', [ShopCategoryController::class, 'fetch'])->middleware('check.permission:shop_categories,can_view');
                Route::get('fetch-params', [ShopCategoryController::class, 'fetchParams'])->middleware('check.permission:shop_categories,can_view');
                Route::get('fetch-by-field', [ShopCategoryController::class, 'fetchByField'])->middleware('check.permission:shop_categories,can_view');
                Route::post('insert', [ShopCategoryController::class, 'insert'])->middleware('check.permission:shop_categories,can_add');
                Route::put('update', [ShopCategoryController::class, 'update'])->middleware('check.permission:shop_categories,can_edit');
                Route::delete('delete/{id}', [ShopCategoryController::class, 'delete'])->middleware('check.permission:shop_categories,can_delete');
            });

            Route::group(['prefix' => 'shop-products'], function () {
                Route::get('fetch', [ShopProductController::class, 'fetch'])->middleware('check.permission:shop_products,can_view');
                Route::get('fetch-params', [ShopProductController::class, 'fetchParams'])->middleware('check.permission:shop_products,can_view');
                Route::get('fetch-by-field', [ShopProductController::class, 'fetchByField'])->middleware('check.permission:shop_products,can_view');
                Route::post('insert', [ShopProductController::class, 'insert'])->middleware('check.permission:shop_products,can_add');
                Route::put('update', [ShopProductController::class, 'update'])->middleware('check.permission:shop_products,can_edit');
                Route::delete('delete/{id}', [ShopProductController::class, 'delete'])->middleware('check.permission:shop_products,can_delete');
            });

            Route::group(['prefix' => 'shop-product-option-types'], function () {
                Route::get('fetch', [ShopProductOptionTypeController::class, 'fetch'])->middleware('check.permission:shop_product_option_types,can_view');
                Route::get('fetch-by-field', [ShopProductOptionTypeController::class, 'fetchByField'])->middleware('check.permission:shop_product_option_types,can_view');
                Route::post('insert', [ShopProductOptionTypeController::class, 'insert'])->middleware('check.permission:shop_product_option_types,can_add');
                Route::put('update', [ShopProductOptionTypeController::class, 'update'])->middleware('check.permission:shop_product_option_types,can_edit');
                Route::delete('delete/{id}', [ShopProductOptionTypeController::class, 'delete'])->middleware('check.permission:shop_product_option_types,can_delete');
            });

            Route::group(['prefix' => 'shop-product-colors'], function () {
                Route::get('fetch', [ShopProductColorController::class, 'fetch'])->middleware('check.permission:shop_product_colors,can_view');
                Route::get('fetch-by-field', [ShopProductColorController::class, 'fetchByField'])->middleware('check.permission:shop_product_colors,can_view');
                Route::post('insert', [ShopProductColorController::class, 'insert'])->middleware('check.permission:shop_product_colors,can_add');
                Route::put('update', [ShopProductColorController::class, 'update'])->middleware('check.permission:shop_product_colors,can_edit');
                Route::delete('delete/{id}', [ShopProductColorController::class, 'delete'])->middleware('check.permission:shop_product_colors,can_delete');
            });

            Route::group(['prefix' => 'media'], function () {
                Route::get('fetch', [MediaController::class, 'fetch'])->middleware('check.permission:medias,can_view');
                Route::get('fetch-params', [MediaController::class, 'fetchParams'])->middleware('check.permission:medias,can_view');
                Route::get('fetch-by-field', [MediaController::class, 'fetchByField'])->middleware('check.permission:medias,can_view');
                Route::post('upload', [MediaController::class, 'upload'])->middleware('check.permission:medias,can_add');
                Route::post('upload-file', [MediaController::class, 'uploadFile'])->middleware('check.permission:medias,can_add');
                Route::post('replace-image', [MediaController::class, 'replaceImage'])->middleware('check.permission:medias,can_edit');
                Route::post('translate-ai', [MediaController::class, 'translateAI'])->middleware('check.permission:medias,can_edit');
                Route::put('update', [MediaController::class, 'update'])->middleware('check.permission:medias,can_edit');
                Route::delete('delete/{ids}', [MediaController::class, 'delete'])->middleware('check.permission:medias,can_delete');
                Route::get('export', [MediaController::class, 'export'])->middleware('check.permission:medias,can_export');
            });

            Route::group(['prefix' => 'vcountries'], function () {
                Route::get('fetch', [AllCountryController::class, 'fetch'])->middleware('only.for.superadmin');
                Route::get('fetch-by-field', [AllCountryController::class, 'fetchByField'])->middleware('only.for.superadmin');

                Route::post('store', [AllCountryController::class, 'store'])->middleware('only.for.superadmin');
                Route::put('update', [AllCountryController::class, 'update'])->middleware('only.for.superadmin');
                Route::delete('delete/{id}', [AllCountryController::class, 'delete'])->middleware('only.for.superadmin');
            });


            Route::group(['prefix' => 'vendors'], function () {
                Route::get('fetch', [VendorController::class, 'fetch'])->middleware('only.for.superadmin');
                Route::get('fetch-by-field', [VendorController::class, 'fetchByField'])->middleware('only.for.superadmin');
                Route::get('fetch-params', [VendorController::class, 'fetchParams'])->middleware('only.for.superadmin');

                Route::post('store', [VendorController::class, 'store'])->middleware('only.for.superadmin');
                Route::put('update', [VendorController::class, 'update'])->middleware('only.for.superadmin');
                Route::delete('delete/{id}', [VendorController::class, 'delete'])->middleware('only.for.superadmin');
            });

            Route::group(['prefix' => 'permalinks'], function () {
                // get requests
                Route::get('fetch', [PermalinkController::class, 'fetch'])->middleware('check.permission:permalinks,can_view');
                Route::get('fetch-params', [PermalinkController::class, 'fetchParams'])->middleware('check.permission:permalinks,can_view');

                // post requests
                Route::put('update', [PermalinkController::class, 'update'])->middleware('check.permission:permalinks,can_edit');
            });

            Route::group(['prefix' => 'cookie-settings'], function () {
                // get requests
                Route::get('fetch', [CookieSettingController::class, 'fetch'])->middleware('check.permission:cookie_settings,can_view');
                Route::get('fetch-params', [CookieSettingController::class, 'fetchParams'])->middleware('check.permission:cookie_settings,can_view');

                // post requests
                Route::put('update', [CookieSettingController::class, 'update'])->middleware('check.permission:cookie_settings,can_edit');
            });

            Route::group(['prefix' => 'permissions'], function () {
                Route::get('fetch', [PermissionController::class, 'fetch'])->middleware('check.permission:permissions,can_view');
                Route::put('update', [PermissionController::class, 'update'])->middleware('check.permission:permissions,can_edit');
            });

            Route::group(['prefix' => 'dashboard'], function () {
                Route::get('fetch-boxes', [DashboardController::class, 'fetchBoxes'])->middleware('check.permission:dashboard,can_view');
                Route::get('fetch-orders-chart', [DashboardController::class, 'fetchOrdersChart'])->middleware('check.permission:dashboard,can_view');
                Route::get('fetch-customers-chart', [DashboardController::class, 'fetchCustomersChart'])->middleware('check.permission:dashboard,can_view');
                Route::get('fetch-billing-countries-chart', [DashboardController::class, 'fetchBillingCountriesChart'])->middleware('check.permission:dashboard,can_view');
                Route::get('fetch-shipping-countries-chart', [DashboardController::class, 'fetchShippingCountriesChart'])->middleware('check.permission:dashboard,can_view');
                Route::get('fetch-payment-methods-chart', [DashboardController::class, 'fetchPaymentMethodsChart'])->middleware('check.permission:dashboard,can_view');
                Route::get('fetch-languages-chart', [DashboardController::class, 'fetchLanguagesChart'])->middleware('check.permission:dashboard,can_view');
                Route::get('fetch-top-products', [DashboardController::class, 'fetchTopProducts'])->middleware('check.permission:dashboard,can_view');
                Route::get('fetch-top-payment-methods', [DashboardController::class, 'fetchTopPaymentMethods'])->middleware('check.permission:dashboard,can_view');
                Route::get('fetch-top-items', [DashboardController::class, 'fetchTopItems'])->middleware('check.permission:dashboard,can_view');
            });

            Route::group(['prefix' => 'user-groups'], function () {
                // get requests
                Route::get('fetch', [UserGroupController::class, 'fetch'])->middleware('check.permission:users_groups,can_view');
                Route::get('fetch-by-field', [UserGroupController::class, 'fetchByField'])->middleware('check.permission:users_groups,can_view');

                // post requests
                Route::post('insert', [UserGroupController::class, 'insert'])->middleware('check.permission:users_groups,can_add');
                Route::put('update', [UserGroupController::class, 'update'])->middleware('check.permission:users_groups,can_edit');
                Route::delete('delete/{id}', [UserGroupController::class, 'delete'])->middleware('check.permission:users_groups,can_delete');
            });
            Route::group(['prefix' => 'member-groups'], function () {
                // get requests
                Route::get('fetch', [MemberGroupController::class, 'fetch'])->middleware('check.permission:affiliate_member_groups,can_view');
                Route::get('fetch-by-field', [MemberGroupController::class, 'fetchByField'])->middleware('check.permission:affiliate_member_groups,can_view');

                // post requests
                Route::post('insert', [MemberGroupController::class, 'insert'])->middleware('check.permission:affiliate_member_groups,can_add');
                Route::put('update', [MemberGroupController::class, 'update'])->middleware('check.permission:affiliate_member_groups,can_edit');
                Route::delete('delete/{id}', [MemberGroupController::class, 'delete'])->middleware('check.permission:affiliate_member_groups,can_delete');
            });

            Route::group(['prefix' => 'programs'], function () {
                // get requests
                Route::get('fetch', [ProgramController::class, 'fetch'])->middleware('check.permission:programs,can_view');
                Route::get('fetch-by-field', [ProgramController::class, 'fetchByField'])->middleware('check.permission:programs,can_view');

                // post requests
                Route::post('insert', [ProgramController::class, 'insert'])->middleware('check.permission:programs,can_add');
                Route::put('update', [ProgramController::class, 'update'])->middleware('check.permission:programs,can_edit');
                Route::delete('delete/{id}', [ProgramController::class, 'delete'])->middleware('check.permission:programs,can_delete');
            });

            Route::group(['prefix' => 'customer-groups'], function () {
                // get requests
                Route::get('fetch', [CustomerGroupController::class, 'fetch'])->middleware('check.permission:customer_groups,can_view');
                Route::get('fetch-by-field', [CustomerGroupController::class, 'fetchByField'])->middleware('check.permission:customer_groups,can_view');

                // post requests
                Route::post('insert', [CustomerGroupController::class, 'insert'])->middleware('check.permission:customer_groups,can_add');
                Route::put('update', [CustomerGroupController::class, 'update'])->middleware('check.permission:customer_groups,can_edit');
                Route::delete('delete/{id}', [CustomerGroupController::class, 'delete'])->middleware('check.permission:customer_groups,can_delete');
            });

            Route::group(['prefix' => 'users'], function () {
                // get requests
                Route::get('fetch', [UserController::class, 'fetch'])->middleware('check.permission:users,can_view');
                Route::get('fetch-by-field', [UserController::class, 'fetchByField'])->middleware('check.permission:users,can_view');
                Route::get('fetch-params', [UserController::class, 'fetchParams'])->middleware('check.permission:users,can_view');
                Route::get('autocomplete', [UserController::class, 'autocomplete']);
                // post requests
                Route::post('insert', [UserController::class, 'insert'])->middleware('check.permission:users,can_add');
                Route::put('update', [UserController::class, 'update'])->middleware('check.permission:users,can_edit');
                Route::delete('delete/{id}', [UserController::class, 'delete'])->middleware('check.permission:users,can_delete');
                Route::post('upload', [UserController::class, 'upload'])->middleware('check.permission:users,can_upload');
                Route::get('export', [UserController::class, 'export'])->middleware('check.permission:users,can_export');
            });

            Route::group(['prefix' => 'customers'], function () {
                // get requests
                Route::get('fetch', [UserController::class, 'fetch'])->middleware('check.permission:customers,can_view');
                Route::get('fetch-by-field', [UserController::class, 'fetchByField'])->middleware('check.permission:customers,can_view');
                Route::get('fetch-params', [UserController::class, 'fetchParams'])->middleware('check.permission:customers,can_view');
                Route::get('autocomplete', [UserController::class, 'autocomplete']);
                Route::get('interests', [UserController::class, 'interests']);
                // post requests
                Route::post('insert', [UserController::class, 'insert'])->middleware('check.permission:customers,can_add');
                Route::put('update', [UserController::class, 'update'])->middleware('check.permission:customers,can_edit');
                Route::delete('delete/{id}', [UserController::class, 'delete'])->middleware('check.permission:customers,can_delete');
                Route::post('upload', [UserController::class, 'upload'])->middleware('check.permission:customers,can_upload');
                Route::get('export', [UserController::class, 'export'])->middleware('check.permission:customers,can_export');
            });

            Route::group(['prefix' => 'general-settings'], function () {
                // get requests
                Route::get('fetch', [GeneralSettingController::class, 'fetch'])->middleware('check.permission:general_settings,can_view');
                Route::get('fetch-by-field', [GeneralSettingController::class, 'fetchByField'])->middleware('check.permission:general_settings,can_view');
                Route::get('fetch-params', [GeneralSettingController::class, 'fetchParams'])->middleware('check.permission:general_settings,can_view');

                // post requests
                Route::put('update', [GeneralSettingController::class, 'update'])->middleware('check.permission:general_settings,can_edit');

                Route::post('delete-cache', [GeneralSettingController::class, 'deleteCache'])->middleware('check.permission:general_settings,can_view');
            });
        });
});
