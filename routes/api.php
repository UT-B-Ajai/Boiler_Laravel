<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PermissionController;

// 🔐 Public Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// 🔒 Protected Routes (requires auth)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // 👤 User CRUD
    Route::apiResource('users', UserController::class);

    // 🛡️ Role CRUD
    Route::apiResource('roles', RoleController::class);


    // 🛡️ Permission CRUD
    Route::apiResource('permissions', PermissionController::class);
    Route::post('assign-permissions', [ PermissionController::class, 'assignPermissions']);
    Route::post('revoke-permissions', [ PermissionController::class, 'revokePermissions']);

    Route::post('/send-mail', [MailController::class, 'sendCustomMail']);

});
