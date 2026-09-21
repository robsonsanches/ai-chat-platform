<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ConversationController;

Route::prefix('v1')->group(function () {

    // Public routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::get('/refresh', [AuthController::class, 'refresh']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/logout-all', [AuthController::class, 'logoutAll']);
            Route::patch('/profile', [AuthController::class, 'updateProfile']);
        });

        Route::prefix('conversations')->group(function () {
            Route::get('/{conversationId}/messages', [ConversationController::class, 'messages']);
            Route::get('/{id}', [ConversationController::class, 'show']);
            Route::patch('/{id}', [ConversationController::class, 'update']);
            Route::delete('/{id}', [ConversationController::class, 'destroy']);
            Route::post('/', [ConversationController::class, 'store']);
            Route::get('/', [ConversationController::class, 'index']);
        });
    });
});