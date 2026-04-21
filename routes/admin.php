<?php

use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserTokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
        Route::post('/login', [AuthController::class, 'login'])->name('admin.login');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        // Users
        Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('admin.users.show');

        // User Tokens
        Route::get('/users/{id}/tokens/add', [UserTokenController::class, 'addForm'])->name('admin.users.tokens.add');
        Route::post('/users/{id}/tokens/add', [UserTokenController::class, 'add'])->name('admin.users.tokens.add.post');
        Route::get('/users/{id}/tokens/deduct', [UserTokenController::class, 'deductForm'])->name('admin.users.tokens.deduct');
        Route::post('/users/{id}/tokens/deduct', [UserTokenController::class, 'deduct'])->name('admin.users.tokens.deduct.post');
        Route::get('/users/{id}/tokens/transactions', [UserTokenController::class, 'transactions'])->name('admin.users.tokens.transactions');

        // Packages
        Route::resource('packages', PackageController::class)->names('admin.packages');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
    });
});
