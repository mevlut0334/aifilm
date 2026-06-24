<?php

use App\Http\Controllers\Api\Auth\AccountController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\V1\AppStoreWebhookController;
use App\Http\Controllers\Api\V1\CustomVideoRequestController;
use App\Http\Controllers\Api\V1\GenerationRequestController;
use App\Http\Controllers\Api\V1\GooglePlayWebhookController;
use App\Http\Controllers\Api\V1\MobilePackageApiController;
use App\Http\Controllers\Api\V1\PackageController;
use App\Http\Controllers\Api\V1\PurchaseController;
use App\Http\Controllers\Api\V1\TemplateController;
use App\Http\Controllers\Api\V1\TokenController;
use App\Http\Middleware\ApiKeyMiddleware;
use App\Http\Controllers\Api\V1\SliderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CustomImageApiController;

Route::prefix('v1')->middleware(['setLocaleFromHeader', ApiKeyMiddleware::class])->group(function () {
    Route::get('/health', function () {
        return response()->json(['status' => 'ok', 'locale' => app()->getLocale()]);
    });

    Route::get('/sliders', [SliderController::class, 'index']);

    // Templates
    Route::get('/templates', [TemplateController::class, 'index']);
    Route::get('/templates/{uuid}', [TemplateController::class, 'show']);

    // Mobile Packages (public — auth gerekmez, satın alma için giriş gerekir)
    Route::get('/mobile-packages', [MobilePackageApiController::class, 'index']);

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Webhooks (auth olmadan, kendi doğrulama mekanizmalarıyla korunuyor)
    Route::post('/webhooks/apple', [AppStoreWebhookController::class, 'handle']);
    Route::post('/webhooks/google', [GooglePlayWebhookController::class, 'handle']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'user']);

        // Account
        Route::delete('/account', [AccountController::class, 'destroy']);

        // Tokens
        Route::get('/tokens/balance', [TokenController::class, 'balance']);
        Route::get('/tokens/transactions', [TokenController::class, 'transactions']);

        // Packages
        Route::get('/packages', [PackageController::class, 'index']);

        // Generation Requests
        Route::get('/generation-requests', [GenerationRequestController::class, 'index']);
        Route::post('/generation-requests', [GenerationRequestController::class, 'store']);
        Route::get('/generation-requests/{uuid}', [GenerationRequestController::class, 'show']);
        Route::delete('/generation-requests/{uuid}', [GenerationRequestController::class, 'destroy']);

        // Custom Image Requests
        Route::get('/custom-image-requests', [CustomImageApiController::class, 'index']);
        Route::post('/custom-image-requests', [CustomImageApiController::class, 'store']);

        // Custom Video Requests
        Route::get('/custom-video-requests', [CustomVideoRequestController::class, 'index']);
        Route::post('/custom-video-requests', [CustomVideoRequestController::class, 'store']);
        Route::get('/custom-video-requests/{uuid}', [CustomVideoRequestController::class, 'show']);
        Route::delete('/custom-video-requests/{uuid}', [CustomVideoRequestController::class, 'destroy']);
        Route::post('/custom-video-requests/{uuid}/segments/{segmentId}/edit', [CustomVideoRequestController::class, 'requestSegmentEdit']);

        // Purchases — tek seferlik
        Route::post('/purchases/ios/verify', [PurchaseController::class, 'verifyIOS']);
        Route::post('/purchases/android/verify', [PurchaseController::class, 'verifyAndroid']);

        // Purchases — abonelik
        Route::post('/purchases/ios/subscribe', [PurchaseController::class, 'subscribeIOS']);
        Route::post('/purchases/android/subscribe', [PurchaseController::class, 'subscribeAndroid']);

        // Abonelik durumu
        Route::get('/subscriptions/status', [PurchaseController::class, 'subscriptionStatus']);
    });
});
