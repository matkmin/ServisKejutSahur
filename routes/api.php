<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register/agent', [App\Http\Controllers\Api\AuthController::class, 'registerAgent'])->middleware('throttle:10,1');
Route::post('/register/member', [App\Http\Controllers\Api\AuthController::class, 'registerMember'])->middleware('throttle:10,1');
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login'])->middleware('throttle:10,1');
Route::get('/agent/lookup', [App\Http\Controllers\Api\AuthController::class, 'lookupAgent']);

// Email Verification (Public because user clicks link from email)
Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\Api\AuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/resend', [App\Http\Controllers\Api\AuthController::class, 'resendVerification'])
    ->middleware('throttle:6,1')
    ->name('verification.resend');

// Password Reset
Route::post('/forgot-password', [App\Http\Controllers\Api\PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password', [App\Http\Controllers\Api\PasswordResetController::class, 'reset']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification link sent!']);
    })->name('verification.send');

    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::get('/me', [App\Http\Controllers\Api\AuthController::class, 'me']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/agent/members', [App\Http\Controllers\Api\AuthController::class, 'getMembers']);
    Route::post('/member/sahur-time', [App\Http\Controllers\Api\AuthController::class, 'updateSahurTime']);

    // Notifications
    Route::get('/agent/notifications', [App\Http\Controllers\Api\AuthController::class, 'getNotifications']);
    Route::post('/agent/notifications/read', [App\Http\Controllers\Api\AuthController::class, 'markNotificationsRead']);

    // Stats
    Route::get('/agent/stats', [App\Http\Controllers\Api\StatsController::class, 'index']);

    // Action Logging
    Route::post('/agent/action-log', [App\Http\Controllers\Api\ActionController::class, 'logAction']);
    Route::post('/agent/member/{id}/toggle-payment', [App\Http\Controllers\Api\AuthController::class, 'togglePaymentStatus']);
    Route::post('/agent/member/{id}/toggle-complete', [App\Http\Controllers\Api\ActionController::class, 'toggleComplete']);

    // Location
    Route::post('/agent/location', [App\Http\Controllers\Api\LocationController::class, 'update']);
    Route::get('/agent/ip-location', [App\Http\Controllers\Api\LocationController::class, 'getIpLocation']);

    // QR Upload
    Route::post('/agent/upload-qr', [App\Http\Controllers\Api\AuthController::class, 'uploadQr']);
    Route::delete('/agent/upload-qr', [App\Http\Controllers\Api\AuthController::class, 'deleteQr']);
});

// Test Route (Public for simulation ease, or protected)
Route::post('/test/trigger-reminders', function () {
    \Illuminate\Support\Facades\Artisan::call('app:send-sahur-reminders');
    return response()->json(['message' => 'Reminders triggered successfully']);
});

// Public Stats
Route::get('/public/stats', [App\Http\Controllers\Api\StatsController::class, 'publicStats']);
