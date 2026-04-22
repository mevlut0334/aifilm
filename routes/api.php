<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\V1\PackageController;
use App\Http\Controllers\Api\V1\PurchaseController;
use App\Http\Controllers\Api\V1\TemplateController;
use App\Http\Controllers\Api\V1\TokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('setLocaleFromHeader')->group(function () {
    Route::get('/health', function () {
        return response()->json(['status' => 'ok', 'locale' => app()->getLocale()]);
    });

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'user']);

        // Tokens
        Route::get('/tokens/balance', [TokenController::class, 'balance']);
        Route::get('/tokens/transactions', [TokenController::class, 'transactions']);

        // Packages
        Route::get('/packages', [PackageController::class, 'index']);

        // Templates
        Route::get('/templates', [TemplateController::class, 'index']);
        Route::get('/templates/{uuid}', [TemplateController::class, 'show']);

        // Purchases
        Route::post('/purchases/ios/verify', [PurchaseController::class, 'verifyIOS']);
        Route::post('/purchases/android/verify', [PurchaseController::class, 'verifyAndroid']);
    });
});
