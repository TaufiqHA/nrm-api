<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NadaController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

Route::get('/songs', [SongController::class, 'index']);
Route::get('/songs/{song}', [SongController::class, 'show']);

Route::get('/settings', [SettingController::class, 'index']);

Route::get('/nadas', [NadaController::class, 'index']);
Route::get('/nadas/{nada}', [NadaController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::match(['put', 'patch'], '/profile', [AuthController::class, 'updateProfile']);
    Route::put('/profile/password', [AuthController::class, 'updatePassword']);

    Route::post('/categories', [CategoryController::class, 'store']);
    Route::match(['put', 'patch'], '/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    Route::post('/songs', [SongController::class, 'store']);
    Route::match(['put', 'patch'], '/songs/{song}', [SongController::class, 'update']);
    Route::delete('/songs/{song}', [SongController::class, 'destroy']);

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::match(['put', 'patch'], '/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);

        Route::get('/nadas', [NadaController::class, 'index']);
        Route::post('/nadas', [NadaController::class, 'store']);
        Route::get('/nadas/{nada}', [NadaController::class, 'show']);
        Route::match(['put', 'patch'], '/nadas/{nada}', [NadaController::class, 'update']);
        Route::delete('/nadas/{nada}', [NadaController::class, 'destroy']);

        Route::get('/settings', [SettingController::class, 'index']);
        Route::post('/settings', [SettingController::class, 'save']);
        Route::match(['put', 'patch'], '/settings', [SettingController::class, 'save']);
        Route::get('/settings/{setting}', [SettingController::class, 'show']);
        Route::match(['put', 'patch'], '/settings/{setting}', [SettingController::class, 'update']);
        Route::delete('/settings/{setting}', [SettingController::class, 'destroy']);
    });

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
