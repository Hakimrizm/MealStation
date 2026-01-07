<?php

use App\Http\Controllers\Auth\AuthController;
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
});
