<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    
    // Posts
    Route::apiResource('posts', PostController::class);
    Route::patch('/posts/{post}/approve', [PostController::class, 'approve']);
    Route::patch('/posts/{post}/reject', [PostController::class, 'reject']);
    Route::get('/posts/pending-approval', [PostController::class, 'pendingApproval']);
    Route::get('/posts/analytics', [PostController::class, 'analytics']);
    
    // Accounts
    Route::apiResource('accounts', AccountController::class);
    
    // AI Content Generation
    Route::prefix('ai')->group(function () {
        Route::post('/generate-content', [AiController::class, 'generateContent']);
        Route::post('/generate-hashtags', [AiController::class, 'generateHashtags']);
        Route::post('/generate-ideas', [AiController::class, 'generateIdeas']);
        Route::get('/stats', [AiController::class, 'getStats']);
        Route::get('/models', [AiController::class, 'getModels']);
    });
    
    // Teams
    Route::prefix('teams')->group(function () {
        Route::get('/', [TeamController::class, 'index']);
        Route::post('/', [TeamController::class, 'store']);
        Route::get('/{id}', [TeamController::class, 'show']);
        Route::put('/{id}', [TeamController::class, 'update']);
        Route::post('/{id}/invite', [TeamController::class, 'inviteMember']);
        Route::put('/{teamId}/members/{memberId}', [TeamController::class, 'updateMemberRole']);
        Route::delete('/{teamId}/members/{memberId}', [TeamController::class, 'removeMember']);
        Route::post('/{id}/leave', [TeamController::class, 'leaveTeam']);
        Route::get('/{id}/posts', [TeamController::class, 'getPosts']);
    });
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications/clear-all', [NotificationController::class, 'clearAll']);
    
    // Settings
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::post('/settings', [SettingsController::class, 'store']);
    Route::post('/settings/bulk', [SettingsController::class, 'bulkUpdate']);
    Route::get('/settings/{key}', [SettingsController::class, 'show']);
    Route::put('/settings/{key}', [SettingsController::class, 'update']);
    Route::delete('/settings/{key}', [SettingsController::class, 'destroy']);
    Route::get('/settings/category/{category}', [SettingsController::class, 'getByCategory']);
    Route::post('/settings/reset', [SettingsController::class, 'resetDefaults']);
});
