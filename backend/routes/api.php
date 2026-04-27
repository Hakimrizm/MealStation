<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Public menu
Route::get('/menus', [MenuController::class, 'index']);
Route::get('/menus/{menu}', [MenuController::class, 'show']);


/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Common Auth Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);


    /*
    |--------------------------------------------------------------------------
    | User Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:user')->group(function () {
        Route::get('/user/dashboard', function () {
            return "Dashboard User";
        });

        Route::post('/checkout', [OrderController::class, 'checkout']);
        Route::get('/my/orders', [OrderController::class, 'myOrders']);
        Route::get('/my/orders/{order}', [OrderController::class, 'myOrderShow']);
        Route::post('/my/orders/{order}/pay', [OrderController::class, 'pay']);
    });


    /*
    |--------------------------------------------------------------------------
    | Tenant Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:tenant')->group(function () {
        Route::get('/tenant/dashboard', function () {
            return "Dashboard Tenant";
        });

        // Menu management
        Route::post('/menus', [MenuController::class, 'store']);
        Route::put('/menus/{menu}', [MenuController::class, 'update']);
        Route::delete('/menus/{menu}', [MenuController::class, 'destroy']);
        Route::get('/tenant/menus', [MenuController::class, 'myMenus']);

        // Order management
        Route::get('/tenant/orders', [OrderController::class, 'tenantOrders']);
        Route::patch('/tenant/orders/{order}/status', [OrderController::class, 'tenantUpdateStatus']);
        Route::patch('/tenant/orders/{order}/payment', [OrderController::class, 'tenantVerifyPayment']);

        // QRIS management
        Route::post('/tenant/qris', [AuthController::class, 'uploadQris']);
        Route::delete('/tenant/qris', [AuthController::class, 'removeQris']);
    });
});