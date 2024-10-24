<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::group(['middleware' => ['role:admin']], function () {
    // Only admins can access these routes
    Route::get('/admin', [AdminController::class, 'index']);
});

Route::group(['middleware' => ['role:user']], function () {
    // Only regular users can access these routes
    Route::get('/user', [UserController::class, 'index']);
});

require __DIR__.'/auth.php';