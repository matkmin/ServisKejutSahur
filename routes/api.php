<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register/agent', [App\Http\Controllers\Api\AuthController::class, 'registerAgent']);
Route::post('/register/member', [App\Http\Controllers\Api\AuthController::class, 'registerMember']);
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
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
});

// Test Route (Public for simulation ease, or protected)
Route::post('/test/trigger-reminders', function () {
    \Illuminate\Support\Facades\Artisan::call('app:send-sahur-reminders');
    return response()->json(['message' => 'Reminders triggered successfully']);
});
