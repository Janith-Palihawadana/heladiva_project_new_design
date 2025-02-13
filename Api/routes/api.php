<?php
use App\Http\Controllers\AgencyController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\SaleRefController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'auth'
], function () {
    Route::get('/logout', [AuthController::class, 'logout']); // web
    Route::post('/login', [AuthController::class, 'login']); //web
    Route::post('/reset_password', [AuthController::class, 'resetPassword']); // mobile
    Route::post('/reset_password_admin', [AuthController::class, 'resetPasswordAdmin']); // web
});

Route::group([
    'prefix' => 'users',
    'middleware' => 'auth:api',
], function () {
    Route::post('/get-user-roles', [UserRoleController::class, 'getUserRole']); // web
    Route::post('/save-user-role', [UserRoleController::class, 'saveUserRole']); // web
    Route::post('/get-role-permission', [UserRoleController::class, 'getRolePermission']); // web
    Route::post('/update-user-role', [UserRoleController::class, 'updateUsers']); // web
    Route::delete('/delete-user-role', [UserRoleController::class,'deleteUseRole']); // web
    Route::post('/get-users', [UserRoleController::class, 'getUsers']); // web
    Route::post('/save-user', [UserRoleController::class, 'saveUser']); // web
    Route::post('/update-user', [UserRoleController::class, 'updateUser']); // web
    Route::delete('/delete-user', [UserRoleController::class,'deleteUse']); // web
});

Route::group([
    'prefix' => 'agencies',
    'middleware' => 'auth:api',
], function () {
    Route::get('/get-agencies-list', [AgencyController::class, 'getAgencyList']); // web
});

Route::group([
    'prefix' => 'areas',
    'middleware' => 'auth:api',
], function () {
    Route::post('/get-areas_list', [AreaController::class, 'getAreasList']); // web
    Route::post('/save-area', [AreaController::class, 'saveArea']); // web
    Route::post('/edit-area', [AreaController::class, 'updateArea']); // web
    Route::delete('/delete-area', [AreaController::class,'deleteArea']); // web
});

Route::group([
    'prefix' => 'shops',
    'middleware' => 'auth:api',
], function () {
    Route::post('/get-shop_list', [ShopController::class, 'getShopsList']); // web
    Route::post('/save-shop', [ShopController::class, 'saveShop']); // web
    Route::post('/edit-shop', [ShopController::class, 'updateShop']); // web
    Route::delete('/delete-shop', [ShopController::class,'deleteShop']); // web
    Route::get('/get-shop-details', [ShopController::class, 'getShopDetails']); // web
});

Route::group([
    'prefix' => 'routes',
    'middleware' => 'auth:api',
], function () {
    Route::post('/get-routes_list', [RouteController::class, 'getRoutesList']); // web
    Route::post('/save-route', [RouteController::class, 'saveRoute']); // web
    Route::post('/edit-route', [RouteController::class, 'updateRoutes']); // web
    Route::delete('/delete-route', [RouteController::class,'deleteRoutes']); // web
    Route::get('/get-route-details', [RouteController::class, 'getRouteDetails']); // web
});

Route::group([
    'prefix' => 'sale-ref',
    'middleware' => 'auth:api',
], function () {
    Route::post('/get-sale-ref-list',[SaleRefController::class, 'getSaleRefList']); // web
    Route::post('/save-sale-ref', [SaleRefController::class, 'saveSaleRef']); // web
    Route::post('/edit-sale-ref', [SaleRefController::class, 'updateSaleRef']); // web
    Route::delete('/delete-sale-ref', [SaleRefController::class,'deleteSaleRef']); // web
});

Route::group([
    'prefix' => 'vehicles',
    'middleware' => 'auth:api',
], function () {
    Route::post('/get-vehicles_list', [VehicleController::class, 'getVehiclesList']); // web
    Route::post('/save-vehicle', [VehicleController::class, 'saveVehicle']); // web
    Route::post('/edit-vehicle', [VehicleController::class, 'updateVehicle']); // web
    Route::delete('/delete-vehicle', [VehicleController::class,'deleteVehicle']); // web
});

Route::group([
    'prefix' => 'invoices',
    'middleware' => 'auth:api',
], function () {
    Route::post('/get-invoice-list', [InvoiceController::class, 'getInvoicesList']); // web
    Route::post('/save-invoice', [InvoiceController::class, 'saveInvoice']); // web
    Route::post('/edit-invoice', [InvoiceController::class, 'updateInvoice']); // web
    Route::delete('/delete-invoice', [InvoiceController::class,'deleteInvoice']); // web
});
