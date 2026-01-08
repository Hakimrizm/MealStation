<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\MenuController;
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
    Route::put('/menus/{menu}', [MenuController::class, 'update']);
    Route::delete('/menus/{menu}', [MenuController::class, 'destroy']);
    Route::middleware(['auth:sanctum', 'role:tenant'])->get(
        '/tenant/menus',
        [MenuController::class, 'myMenus']
    );
});
