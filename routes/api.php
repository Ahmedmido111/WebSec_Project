<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::middleware('api')->get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

// Public routes - no authentication required
Route::post('/login', [AuthController::class, 'login']);

// Protected routes - require authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [AuthController::class, 'users']); // Changed to GET for better RESTful practice
    Route::post('/logout', [AuthController::class, 'logout']);
}); 