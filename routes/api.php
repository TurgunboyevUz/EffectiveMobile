<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/logout', [AuthController::class, 'logout']);

Route::post('user/register', [UserController::class, 'register']);

Route::middleware('auth:sanctum')->group(function(){
    Route::prefix('user')->group(function(){
        Route::get('me', [UserController::class, 'me']);
        Route::put('update', [UserController::class, 'update']);
        Route::delete('delete', [UserController::class, 'delete']);
    });

    Route::apiResource('tasks', TaskController::class);
});
