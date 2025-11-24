<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DummyController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
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
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('logout', 'logout');
        Route::get('permission', 'permission');
        Route::get('session', 'session');
        Route::post('forgot-password', 'forgotPassword');
        Route::post('refresh', 'refresh');
        Route::get('me', 'me');
    });
    Route::prefix('admin')->group(function () {
        Route::prefix('user')->controller(UserController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('store', 'store');
            Route::get('show/{uuid}', 'show');
            Route::post('update/{uuid}', 'update');
            Route::post('update-avatar/{uuid}', 'updateAvatar');
            Route::delete('delete/{uuid}', 'destroy');
            Route::post('update-password/{uuid}', 'updatePassword');
        });
        Route::prefix('categori')->controller(CategoriController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('store', 'store');
            Route::get('show/{uuid}', 'show');
            Route::post('update/{uuid}', 'update');
            Route::delete('delete/{uuid}', 'destroy');
        });
        Route::prefix('post')->controller(PostController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('store', 'store');
            Route::get('show/{uuid}', 'show');
            Route::post('update/{uuid}', 'update');
            Route::delete('delete/{uuid}', 'destroy');
            Route::post('delete-many', 'destroyMany');
        });
        Route::prefix('comment')->controller(CommentController::class)->group(function () {
            Route::get('index', 'index');
        });
    });
});
