<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DummyController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::prefix('dummy')->controller(DummyController::class)->group(function () {
    Route::get('users', 'dummyUsers');
    Route::get('users/{id}', 'dummyUsersId');
    Route::get('product', 'dummyProduct');
    Route::get('product/{id}', 'dummyProductId');
    Route::get('posts', 'dummyPosts');
    Route::get('posts/{id}', 'dummyPostsId');
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/users', [UserController::class, 'index']);
});
