<?php

use App\Http\Controllers\CustomImageController;
use App\Http\Controllers\Web\Auth\AuthController as WebAuthController;
use App\Http\Controllers\Web\GenerationRequestController as WebGenerationRequestController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\PackageController;
use App\Http\Controllers\Web\PaddleWebhookController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\TemplateController;
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

    // Public templates pages (no auth required)
    Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::get('/templates/{uuid}', [TemplateController::class, 'show'])->name('templates.show');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update']);

        // Generation Requests
        Route::get('/generation-requests', [WebGenerationRequestController::class, 'index'])->name('generation-requests.index');
        Route::get('/generation-requests/create', [WebGenerationRequestController::class, 'create'])->name('generation-requests.create');
        Route::post('/generation-requests', [WebGenerationRequestController::class, 'store'])->name('generation-requests.store');
        Route::get('/generation-requests/{uuid}', [WebGenerationRequestController::class, 'show'])->name('generation-requests.show');
        Route::delete('/generation-requests/{uuid}', [WebGenerationRequestController::class, 'destroy'])->name('generation-requests.destroy');

        // Custom Images
        Route::get('/custom-images', [CustomImageController::class, 'index'])->name('custom-images.index');
        Route::get('/custom-images/create', [CustomImageController::class, 'create'])->name('custom-images.create');
        Route::post('/custom-images', [CustomImageController::class, 'store'])->name('custom-images.store');
        Route::get('/custom-images/{uuid}', [CustomImageController::class, 'show'])->name('custom-images.show');
    });
});

// Paddle Webhook (outside locale group)
Route::post('/webhook/paddle', [PaddleWebhookController::class, 'handle'])->name('webhook.paddle');
