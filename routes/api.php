<?php

use App\Http\Controllers\API\TagsController;
use App\Http\Controllers\API\TasksController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UsersController;

Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:api')->post('logout', [AuthController::class, 'logout']);

Route::middleware(['auth:api', 'api'])->group(function() {
    Route::controller(UsersController::class)->prefix('users')->group(function() {
        Route::get('', 'list');
    });

    Route::controller(TasksController::class)->prefix('tasks')->group(function() {
        Route::get('', 'list');
        Route::post('', 'create');
        Route::get('{id}', 'get');
        Route::put('{id}', 'update');
        Route::delete('{id}', 'delete');
        Route::patch('{id}/toggle-status', 'toggleStatus');
        Route::patch('{id}/restore', 'restore');
    });

    Route::controller(TagsController::class)->prefix('tags')->group(function() {
        Route::get('', 'list');
        Route::post('', 'create');
        Route::put('{id}', 'update');
        Route::delete('{id}', 'delete');
    });
});
