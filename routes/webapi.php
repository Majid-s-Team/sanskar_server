<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebAPI\UserController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\RoleController;

// Route::prefix('webapi')->middleware(['auth:sanctum'])->group(function () {
Route::middleware('auth:sanctum')->group(function () {

    // Route::get('/users', [UserController::class, 'index']);
    // Route::post('/users', [UserController::class, 'store']);
    // Route::get('/users/{id}', [UserController::class, 'show']);
    // Route::put('/users/{id}', [UserController::class, 'update']);
    // Route::delete('/users/{id}', [UserController::class, 'destroy']);
    // Route::patch('/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);


    // Role Management
    Route::get('roles', [RoleController::class, 'index']);
    Route::post('roles', [RoleController::class, 'store']);
    Route::delete('roles/{id}', [RoleController::class, 'destroy']);


    // Permission management
    Route::get('permissions', [PermissionController::class, 'index']);
    Route::post('permissions', [PermissionController::class, 'store']);
    Route::delete('permissions/{id}', [PermissionController::class, 'destroy']);

    // Assign role/permission to user
    Route::post('assign-role/{userId}', [PermissionController::class, 'assignRole']);
    Route::post('assign-permission/{userId}', [PermissionController::class, 'assignPermission']);
    // Route::post('/roles/assign-permission', [PermissionController::class, 'assignPermissionToRole']);
    Route::post('assign-multiple-permissions/{userId}', [PermissionController::class, 'assignMultiplePermissions']);
    // Route::post('/roles/assign-permissions', [PermissionController::class, 'assignMultiplePermissionsToRole']);
    Route::get('/user/{id}/permissions', [PermissionController::class, 'getUserRolesAndPermissions']);
    Route::get('/my-permissions', [PermissionController::class, 'getMyPermissionsAndRoles']);


});

