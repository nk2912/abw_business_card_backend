<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\BusinessCardController;

/*
|--------------------------------------------------------------------------
| Public APIs (NO AUTH REQUIRED)
|--------------------------------------------------------------------------
*/

Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/complete-register', [AuthController::class, 'completeRegister']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// business cards
Route::get('/business-cards', [BusinessCardController::class, 'index']);
Route::get('/business-cards/{id}', [BusinessCardController::class, 'show'])->whereNumber('id');

/*
|--------------------------------------------------------------------------
| Protected APIs (SANCTUM REQUIRED)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/me', [AuthController::class, 'me']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    Route::post('/deactivate-account', [AuthController::class, 'deactivateAccount']);

    // Company
    Route::get('/companies', [CompanyController::class, 'index']);
    Route::get('/companies/{company}', [CompanyController::class, 'show']);
    Route::post('/companies', [CompanyController::class, 'store']);
    Route::put('/companies/{company}', [CompanyController::class, 'update']);
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy']);

    //Business-cards
    Route::get('/my-business-cards', [BusinessCardController::class, 'myCards']);
    Route::get('/business-cards/search', [BusinessCardController::class, 'search']); // Add Search
    Route::get('/business-cards/friend-requests', [BusinessCardController::class, 'friendRequests']);
    Route::post('/business-cards/scan-qr', [BusinessCardController::class, 'scanQr']); // Add Scan
    Route::post('/business-cards', [BusinessCardController::class, 'store']);
    Route::post('/business-cards/{id}/add-friend', [BusinessCardController::class, 'addFriend']); // Add Friend
    Route::post('/business-cards/{id}/accept-friend', [BusinessCardController::class, 'acceptFriendRequest'])->whereNumber('id');
    Route::post('/business-cards/{id}/reject-friend', [BusinessCardController::class, 'rejectFriendRequest'])->whereNumber('id');
    Route::post('/business-cards/{id}/unfriend', [BusinessCardController::class, 'removeFriend'])->whereNumber('id');
    Route::put('/business-cards/{id}', [BusinessCardController::class, 'update'])->whereNumber('id');
    Route::delete('/business-cards/{id}', [BusinessCardController::class, 'destroy'])->whereNumber('id');
});
