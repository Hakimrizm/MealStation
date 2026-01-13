<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);

    Route::middleware('role:user')->get('/user/dashboard', function () {
        return "Dashboard User";
    });

    Route::middleware('role:tenant')->get('/tenant/dashboard', function () {
        return "Dashboard Tenant";
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/menus', [MenuController::class, 'store']);
    // Public Menu all
    Route::get('/menus', [MenuController::class, 'index']);
    Route::put('/menus/{menu}', [MenuController::class, 'update']);
    Route::delete('/menus/{menu}', [MenuController::class, 'destroy']);
    Route::middleware(['auth:sanctum', 'role:tenant'])->get(
        '/tenant/menus',
        [MenuController::class, 'myMenus']
    );
    Route::get('/menus/{menu}', [MenuController::class, 'show']);

    // USER
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/my/orders', [OrderController::class, 'myOrders']);
    Route::get('/my/orders/{order}', [OrderController::class, 'myOrderShow']);

    // TENANT
    Route::middleware('role:tenant')->group(function () {
        Route::get('/tenant/orders', [OrderController::class, 'tenantOrders']);
        Route::patch('/tenant/orders/{order}/status', [OrderController::class, 'tenantUpdateStatus']);
    });

    Route::post('/my/orders/{order}/pay', [OrderController::class, 'pay']);
    Route::middleware('role:tenant')->patch('/tenant/orders/{order}/payment', [OrderController::class, 'tenantVerifyPayment']);
});
