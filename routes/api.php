<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/user', [UserController::class, 'updateProfile']);
    Route::delete('/user', [UserController::class, 'deleteAccount'])->name('user.delete');
    Route::get('/user', [UserController::class, 'getUser']);
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('admin');
});

Route::apiResource('wallets', WalletController::class)->middleware('auth');
Route::resource('categories', CategoryController::class)->middleware('auth');
Route::resource('transactions', TransactionController::class)->middleware('auth');
Route::get('/wallets/{wallet}', [WalletController::class, 'show'])->middleware('auth');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
});
