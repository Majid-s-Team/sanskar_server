<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PasswordController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\MediaUploadController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\WebAPI\ActivityController;
use App\Http\Controllers\WebAPI\GradeController;
use App\Http\Controllers\WebAPI\GurukalController;
use App\Http\Controllers\WebAPI\TeeshirtSizeController;
use App\Http\Controllers\API\StudentController;
use App\Http\Controllers\API\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('signup', [AuthController::class, 'signup']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [PasswordController::class, 'forgotPassword']);
Route::post('verify-otp', [PasswordController::class, 'verifyOtp']);
Route::post('reset-password', [PasswordController::class, 'resetPassword']);
Route::get('/user/{id}/students', [StudentController::class, 'getMyStudents']);
Route::post('/stripe/webhook', [PaymentController::class, 'handleStripeWebhook']);
Route::post('/create-stripe-session', [PaymentController::class, 'createStripeSession']);


Route::post('/upload-media', [MediaUploadController::class, 'upload']);
 Route::prefix('activity')->group(function () {
    Route::get('/', [ActivityController::class, 'index']);
    Route::post('/', [ActivityController::class, 'store']);
    Route::get('/{id}', [ActivityController::class, 'show']);
    Route::put('/{id}', [ActivityController::class, 'update']);
    Route::delete('/{id}', [ActivityController::class, 'destroy']);
    Route::patch('/{id}/status', [ActivityController::class, 'changeStatus']);
});

Route::prefix('grade')->group(function () {
    Route::get('/', action: [GradeController::class, 'index']);
    Route::post('/', [GradeController::class, 'store']);
    Route::get('/{id}', [GradeController::class, 'show']);
    Route::put('/{id}', [GradeController::class, 'update']);
    Route::delete('/{id}', [GradeController::class, 'destroy']);
    Route::patch('/{id}/status', [GradeController::class, 'changeStatus']);
});

Route::prefix('gurukal')->group(function () {
    Route::get('/', [GurukalController::class, 'index']);
    Route::post('/', [GurukalController::class, 'store']);
    Route::get('/{id}', [GurukalController::class, 'show']);
    Route::put('/{id}', [GurukalController::class, 'update']);
    Route::delete('/{id}', [GurukalController::class, 'destroy']);
    Route::patch('/{id}/status', [GurukalController::class, 'changeStatus']);
});

Route::prefix('teeshirt-size')->group(function () {
    Route::get('/', [TeeshirtSizeController::class, 'index']);
    Route::post('/', [TeeshirtSizeController::class, 'store']);
    Route::get('/{id}', [TeeshirtSizeController::class, 'show']);
    Route::put('/{id}', [TeeshirtSizeController::class, 'update']);
    Route::delete('/{id}', [TeeshirtSizeController::class, 'destroy']);
    Route::patch('/{id}/status', [TeeshirtSizeController::class, 'changeStatus']);
});
Route::prefix('student')->group(function () {
    Route::get('/', [StudentController::class, 'index']);
    Route::post('/', [StudentController::class, 'store']);
    Route::get('/{id}', [StudentController::class, 'show']);
    Route::put('/{id}', [StudentController::class, 'update']);
    Route::delete('/{id}', [StudentController::class, 'destroy']);
    Route::patch('/{id}/status', [StudentController::class, 'changeStatus']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('change-password', [PasswordController::class, 'changePassword']);
    Route::get('profile', [ProfileController::class, 'view']);
    Route::post('profile-update', [ProfileController::class, 'update']);

    // Route::get('roles', [RoleController::class, 'index']);
    // Route::post('roles', [RoleController::class, 'store']);
    // Route::delete('roles/{id}', [RoleController::class, 'destroy']);

    // // Permission management
    // Route::get('permissions', [PermissionController::class, 'index']);
    // Route::post('permissions', [PermissionController::class, 'store']);
    // Route::delete('permissions/{id}', [PermissionController::class, 'destroy']);

    // // Assign role/permission to user
    // Route::post('assign-role/{userId}', [PermissionController::class, 'assignRole']);
    // Route::post('assign-permission/{userId}', [PermissionController::class, 'assignPermission']);
    // Route::post('assign-multiple-permissions/{userId}', [PermissionController::class, 'assignMultiplePermissions']);

   
});

