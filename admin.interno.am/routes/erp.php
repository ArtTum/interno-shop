<?php

use App\Http\Controllers\API\ERP\AllCountryController;
use App\Http\Controllers\API\ERP\AuthController;
use App\Http\Controllers\API\ERP\ClinicController;
use App\Http\Controllers\API\ERP\CookieSettingController;
use App\Http\Controllers\API\ERP\CustomerGroupController;
use App\Http\Controllers\API\ERP\DashboardController;
use App\Http\Controllers\API\ERP\DiseaseController;
use App\Http\Controllers\API\ERP\DoctorsFinalController;
use App\Http\Controllers\API\ERP\ExtendedPriceController;
use App\Http\Controllers\API\ERP\GeneralController;
use App\Http\Controllers\API\ERP\GeneralSettingController;
use App\Http\Controllers\API\ERP\HospitalController;
use App\Http\Controllers\API\ERP\HospitalsBaseController;
use App\Http\Controllers\API\ERP\IncomingController;
use App\Http\Controllers\API\ERP\LanguageController;
use App\Http\Controllers\API\ERP\MemberGroupController;
use App\Http\Controllers\API\ERP\NoteController;
use App\Http\Controllers\API\ERP\OutgoingController;
use App\Http\Controllers\API\ERP\PermalinkController;
use App\Http\Controllers\API\ERP\PermissionController;
use App\Http\Controllers\API\ERP\ProgramController;
use App\Http\Controllers\API\ERP\RecommendationController;
use App\Http\Controllers\API\ERP\SmsBazaController;
use App\Http\Controllers\API\ERP\SmsHistoryController;
use App\Http\Controllers\API\ERP\SmsShablonController;
use App\Http\Controllers\API\ERP\ShopFrontendController;
use App\Http\Controllers\API\ERP\ShopCategoryController;
use App\Http\Controllers\API\ERP\SubscribeController;
use App\Http\Controllers\API\ERP\TrashController;
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
            Route::group(['prefix' => 'hospitals'], function () {
                // get requests
                Route::get('fetch', [HospitalController::class, 'fetch'])->middleware('check.permission:hospitals,can_view');
                Route::get('fetch-by-field', [HospitalController::class, 'fetchByField'])->middleware('check.permission:hospitals,can_view');

                // post requests
                Route::post('insert', [HospitalController::class, 'insert'])->middleware('check.permission:hospitals,can_add');
                Route::put('update', [HospitalController::class, 'update'])->middleware('check.permission:hospitals,can_edit');
                Route::delete('delete/{id}', [HospitalController::class, 'delete'])->middleware('check.permission:hospitals,can_delete');
            });
            Route::group(['prefix' => 'notes'], function () {
                // get requests
                Route::get('fetch', [NoteController::class, 'fetch'])->middleware('check.permission:notes,can_view');
                Route::get('fetch-by-field', [NoteController::class, 'fetchByField'])->middleware('check.permission:notes,can_view');

                // post requests
                Route::post('insert', [NoteController::class, 'insert'])->middleware('check.permission:notes,can_add');
                Route::put('update', [NoteController::class, 'update'])->middleware('check.permission:notes,can_edit');
                Route::delete('delete/{id}', [NoteController::class, 'delete'])->middleware('check.permission:notes,can_delete');
            });
            Route::group(['prefix' => 'sms-shablons'], function () {
                // get requests
                Route::get('fetch', [SmsShablonController::class, 'fetch'])->middleware('check.permission:sms_shablons,can_view');
                Route::get('fetch-by-field', [SmsShablonController::class, 'fetchByField'])->middleware('check.permission:sms_shablons,can_view');

                // post requests
                Route::post('insert', [SmsShablonController::class, 'insert'])->middleware('check.permission:sms_shablons,can_add');
                Route::put('update', [SmsShablonController::class, 'update'])->middleware('check.permission:sms_shablons,can_edit');
                Route::delete('delete/{id}', [SmsShablonController::class, 'delete'])->middleware('check.permission:sms_shablons,can_delete');
            });
            Route::group(['prefix' => 'sms-histories'], function () {
                // get requests
                Route::get('fetch', [SmsHistoryController::class, 'fetch'])->middleware('check.permission:sms_histories,can_view');
                Route::get('fetch-index-params', [SmsHistoryController::class, 'fetchIndexParams'])->middleware('check.permission:sms_histories,can_view');
                Route::post('send', [SmsHistoryController::class, 'send'])->middleware('check.permission:sms_histories,can_add');
//                Route::get('fetch-by-field', [HospitalController::class, 'fetchByField'])->middleware('check.permission:hospitals,can_view');
//
//                // post requests
//                Route::post('insert', [HospitalController::class, 'insert'])->middleware('check.permission:hospitals,can_add');
//                Route::put('update', [HospitalController::class, 'update'])->middleware('check.permission:hospitals,can_edit');
//                Route::delete('delete/{id}', [HospitalController::class, 'delete'])->middleware('check.permission:hospitals,can_delete');
            });

            Route::group(['prefix' => 'diseases'], function () {
                // get requests
                Route::get('fetch', [DiseaseController::class, 'fetch'])->middleware('check.permission:diseases,can_view');
                Route::get('fetch-by-field', [DiseaseController::class, 'fetchByField'])->middleware('check.permission:diseases,can_view');

                // post requests
                Route::post('insert', [DiseaseController::class, 'insert'])->middleware('check.permission:diseases,can_add');
                Route::put('update', [DiseaseController::class, 'update'])->middleware('check.permission:diseases,can_edit');
                Route::delete('delete/{id}', [DiseaseController::class, 'delete'])->middleware('check.permission:diseases,can_delete');
            });

            Route::group(['prefix' => 'doctors-final'], function () {
                // get requests
                Route::get('fetch', [DoctorsFinalController::class, 'fetch'])->middleware('check.permission:doctors_finals,can_view');
                Route::get('fetch-by-field', [DoctorsFinalController::class, 'fetchByField'])->middleware('check.permission:doctors_finals,can_view');

                // post requests
                Route::post('insert', [DoctorsFinalController::class, 'insert'])->middleware('check.permission:doctors_finals,can_add');
                Route::put('update', [DoctorsFinalController::class, 'update'])->middleware('check.permission:doctors_finals,can_edit');
                Route::delete('delete/{id}', [DoctorsFinalController::class, 'delete'])->middleware('check.permission:doctors_finals,can_delete');
            });

            Route::group(['prefix' => 'extended-prices'], function () {
                // get requests
                Route::get('fetch', [ExtendedPriceController::class, 'fetch'])->middleware('check.permission:extended_prices,can_view');
                Route::get('fetch-by-field', [ExtendedPriceController::class, 'fetchByField'])->middleware('check.permission:extended_prices,can_view');

                // post requests
                Route::post('insert', [ExtendedPriceController::class, 'insert'])->middleware('check.permission:extended_prices,can_add');
                Route::put('update', [ExtendedPriceController::class, 'update'])->middleware('check.permission:extended_prices,can_edit');
                Route::delete('delete/{id}', [ExtendedPriceController::class, 'delete'])->middleware('check.permission:extended_prices,can_delete');
            });

            Route::group(['prefix' => 'clinics'], function () {
                // get requests
                Route::get('fetch', [ClinicController::class, 'fetch'])->middleware('check.permission:clinics,can_view');
                Route::get('fetch-by-field', [ClinicController::class, 'fetchByField'])->middleware('check.permission:clinics,can_view');

                // post requests
                Route::post('insert', [ClinicController::class, 'insert'])->middleware('check.permission:clinics,can_add');
                Route::put('update', [ClinicController::class, 'update'])->middleware('check.permission:clinics,can_edit');
                Route::delete('delete/{id}', [ClinicController::class, 'delete'])->middleware('check.permission:clinics,can_delete');
            });

            Route::group(['prefix' => 'sms-bazas'], function () {
                // get requests
                Route::get('fetch', [SmsBazaController::class, 'fetch'])->middleware('check.permission:sms_bazas,can_view');
                Route::get('fetch-index-params', [SmsBazaController::class, 'fetchIndexParams'])->middleware('check.permission:sms_bazas,can_view');
                Route::get('fetch-by-field', [SmsBazaController::class, 'fetchByField'])->middleware('check.permission:sms_bazas,can_view');

                // post requests
                Route::post('insert', [SmsBazaController::class, 'insert'])->middleware('check.permission:sms_bazas,can_add');
                Route::put('update', [SmsBazaController::class, 'update'])->middleware('check.permission:sms_bazas,can_edit');
                Route::delete('delete/{id}', [SmsBazaController::class, 'delete'])->middleware('check.permission:sms_bazas,can_delete');
            });
            Route::group(['prefix' => 'outgoings'], function () {
                // get requests
                Route::get('fetch', [OutgoingController::class, 'fetch'])->middleware('check.permission:outgoings,can_view');
                Route::get('fetch-by-field', [OutgoingController::class, 'fetchByField'])->middleware('check.permission:outgoings,can_view');
                Route::get('fetch-params', [OutgoingController::class, 'fetchParams'])->middleware('check.permission:outgoings,can_view');
                Route::get('fetch-index-params', [OutgoingController::class, 'fetchIndexParams'])->middleware('check.permission:outgoings,can_view');

                // post requests
                Route::post('insert', [OutgoingController::class, 'insert'])->middleware('check.permission:outgoings,can_add');
                Route::put('update', [OutgoingController::class, 'update'])->middleware('check.permission:outgoings,can_edit');
                Route::delete('delete/{id}', [OutgoingController::class, 'delete'])->middleware('check.permission:outgoings,can_delete');
            });
            Route::group(['prefix' => 'subscribes'], function () {
                // get requests
                Route::get('fetch-index-params', [SubscribeController::class, 'fetchIndexParams'])->middleware('check.permission:subscribes,can_view');
                Route::get('fetch', [SubscribeController::class, 'fetch'])->middleware('check.permission:subscribes,can_view');
                Route::get('fetch-by-field', [SubscribeController::class, 'fetchByField'])->middleware('check.permission:subscribes,can_view');
                Route::get('fetch-count', [SubscribeController::class, 'fetchCount'])->middleware('check.permission:subscribes,can_view');

                // post requests
                Route::post('insert', [SubscribeController::class, 'insert'])->middleware('check.permission:subscribes,can_add');
                Route::put('update', [SubscribeController::class, 'update'])->middleware('check.permission:subscribes,can_edit');
                Route::delete('delete/{id}', [SubscribeController::class, 'delete'])->middleware('check.permission:subscribes,can_delete');
            });
            Route::group(['prefix' => 'incomings'], function () {
                // get requests
                Route::get('fetch-index-params', [IncomingController::class, 'fetchIndexParams'])->middleware('check.permission:incomings,can_view');
                Route::get('fetch-params', [IncomingController::class, 'fetchParams'])->middleware('check.permission:incomings,can_view');
                Route::get('fetch', [IncomingController::class, 'fetch'])->middleware('check.permission:incomings,can_view');
                Route::get('export', [IncomingController::class, 'export'])->middleware('check.permission:incomings,can_view');
                Route::get('fetch-stats', [IncomingController::class, 'fetchStats'])->middleware('check.permission:incomings,can_view');
                Route::get('fetch-by-field', [IncomingController::class, 'fetchByField'])->middleware('check.permission:incomings,can_view');

                // post requests
                Route::post('insert', [IncomingController::class, 'insert'])->middleware('check.permission:incomings,can_add');
                Route::put('update', [IncomingController::class, 'update'])->middleware('check.permission:incomings,can_edit');
                Route::delete('delete/{id}', [IncomingController::class, 'delete'])->middleware('check.permission:incomings,can_delete');
            });

            Route::group(['prefix' => 'trash'], function () {
                Route::get('fetch', [TrashController::class, 'fetch'])->middleware('check.permission:trash,can_view');
                Route::post('restore/{id}', [TrashController::class, 'restore'])->middleware('check.permission:trash,can_delete');
                Route::delete('force-delete/{id}', [TrashController::class, 'forceDelete'])->middleware('check.permission:trash,can_delete');
            });

            Route::group(['prefix' => 'hospitals-bases'], function () {
                // get requests
                Route::get('fetch-index-params', [HospitalsBaseController::class, 'fetchIndexParams'])->middleware('check.permission:hospitals_bases,can_view');
                Route::get('fetch-params', [HospitalsBaseController::class, 'fetchParams'])->middleware('check.permission:hospitals_bases,can_view');
                Route::get('fetch', [HospitalsBaseController::class, 'fetch'])->middleware('check.permission:hospitals_bases,can_view');
                Route::get('fetch-by-field', [HospitalsBaseController::class, 'fetchByField'])->middleware('check.permission:hospitals_bases,can_view');

                // post requests
                Route::post('insert', [HospitalsBaseController::class, 'insert'])->middleware('check.permission:hospitals_bases,can_add');
                Route::put('update', [HospitalsBaseController::class, 'update'])->middleware('check.permission:hospitals_bases,can_edit');
                Route::delete('delete/{id}', [HospitalsBaseController::class, 'delete'])->middleware('check.permission:hospitals_bases,can_delete');
            });

            Route::group(['prefix' => 'recommendations'], function () {
                // get requests
                Route::get('fetch-index-params', [RecommendationController::class, 'fetchIndexParams'])->middleware('check.permission:recommendations,can_view');
                Route::get('fetch-params', [RecommendationController::class, 'fetchParams'])->middleware('check.permission:recommendations,can_view');
                Route::get('fetch', [RecommendationController::class, 'fetch'])->middleware('check.permission:recommendations,can_view');
                Route::get('fetch-by-field', [RecommendationController::class, 'fetchByField'])->middleware('check.permission:recommendations,can_view');
                Route::get('export', [RecommendationController::class, 'export'])->middleware('check.permission:recommendations,can_view');

                // post requests
                Route::post('insert', [RecommendationController::class, 'insert'])->middleware('check.permission:recommendations,can_add');
                Route::put('update', [RecommendationController::class, 'update'])->middleware('check.permission:recommendations,can_edit');
                Route::delete('delete/{id}', [RecommendationController::class, 'delete'])->middleware('check.permission:recommendations,can_delete');
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
