<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ActivityController;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // User routes with common prefix
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);          // GET /users
        Route::get('/{id}', [UserController::class, 'show']);      // GET /users/{id}
        Route::post('/', [UserController::class, 'store']);        // POST /users
        Route::put('/{id}', [UserController::class, 'update']);    // PUT /users/{id}
        Route::delete('/{id}', [UserController::class, 'destroy']); // DELETE /users/{id}
        Route::delete('/{id}/force-delete', [UserController::class, 'forceDelete']); // DELETE /users/{id}/force-delete
        Route::post('/{id}/restore', [UserController::class, 'restore']); // POST /users/{id}/restore
    });

    Route::prefix('activities')->group(function () {
        Route::get('/', [ActivityController::class, 'index']);
        Route::get('/{id}', [ActivityController::class, 'show']);
    });

});
