<?php

use App\Http\Controllers\Web\Auth\AuthController as WebAuthController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\PackageController;
use App\Http\Controllers\Web\PaddleWebhookController;
use App\Http\Controllers\Web\ProfileController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

// Web Routes with locale prefix
Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath'],
], function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register']);
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    // Public packages page (no auth required)
    Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update']);
    });
});

// Paddle Webhook (outside locale group)
Route::post('/webhook/paddle', [PaddleWebhookController::class, 'handle'])->name('webhook.paddle');
