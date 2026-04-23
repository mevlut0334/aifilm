<?php

use App\Http\Controllers\Admin\AdminCustomImageController;
use App\Http\Controllers\Admin\AdminCustomVideoController;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GenerationRequestController as AdminGenerationRequestController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TemplateController;
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

        // Templates
        Route::resource('templates', TemplateController::class)->except(['show'])->names('admin.templates');
        Route::post('/templates/{uuid}/toggle-active', [TemplateController::class, 'toggleActive'])->name('admin.templates.toggle-active');
        Route::delete('/templates/{uuid}/videos/{orientation}', [TemplateController::class, 'deleteVideo'])->name('admin.templates.delete-video');

        // Generation Requests
        Route::get('/generation-requests', [AdminGenerationRequestController::class, 'index'])->name('admin.generation-requests.index');
        Route::get('/generation-requests/{uuid}', [AdminGenerationRequestController::class, 'show'])->name('admin.generation-requests.show');
        Route::post('/generation-requests/{uuid}/progress', [AdminGenerationRequestController::class, 'updateProgress'])->name('admin.generation-requests.update-progress');
        Route::post('/generation-requests/{uuid}/status', [AdminGenerationRequestController::class, 'updateStatus'])->name('admin.generation-requests.update-status');

        // Custom Images
        Route::get('/custom-images', [AdminCustomImageController::class, 'index'])->name('admin.custom-images.index');
        Route::get('/custom-images/{uuid}', [AdminCustomImageController::class, 'show'])->name('admin.custom-images.show');
        Route::post('/custom-images/{uuid}/progress', [AdminCustomImageController::class, 'updateProgress'])->name('admin.custom-images.update-progress');
        Route::post('/custom-images/{uuid}/status', [AdminCustomImageController::class, 'updateStatus'])->name('admin.custom-images.update-status');

        // Custom Videos
        Route::get('/custom-videos', [AdminCustomVideoController::class, 'index'])->name('admin.custom-videos.index');
        Route::get('/custom-videos/{uuid}', [AdminCustomVideoController::class, 'show'])->name('admin.custom-videos.show');
        Route::post('/custom-videos/{uuid}/token-cost', [AdminCustomVideoController::class, 'setTokenCost'])->name('admin.custom-videos.set-token-cost');
        Route::post('/custom-videos/{uuid}/segments', [AdminCustomVideoController::class, 'createSegments'])->name('admin.custom-videos.create-segments');
        Route::post('/custom-videos/{uuid}/add-segment', [AdminCustomVideoController::class, 'addSegment'])->name('admin.custom-videos.add-segment');
        Route::post('/custom-videos/{uuid}/status', [AdminCustomVideoController::class, 'updateRequestStatus'])->name('admin.custom-videos.update-status');
        Route::post('/custom-videos/segments/{segmentId}/progress', [AdminCustomVideoController::class, 'updateSegmentProgress'])->name('admin.custom-videos.segments.update-progress');
        Route::post('/custom-videos/segments/{segmentId}/status', [AdminCustomVideoController::class, 'updateSegmentStatus'])->name('admin.custom-videos.segments.update-status');
        Route::post('/custom-videos/segments/{segmentId}/video-url', [AdminCustomVideoController::class, 'updateSegmentVideoUrl'])->name('admin.custom-videos.segments.update-video-url');
        Route::post('/custom-videos/segments/{segmentId}/mark-failed', [AdminCustomVideoController::class, 'markSegmentAsFailed'])->name('admin.custom-videos.segments.mark-failed');

        // Custom Video Edit Requests
        Route::get('/custom-videos/edit-requests', [AdminCustomVideoController::class, 'editRequests'])->name('admin.custom-videos.edit-requests');
        Route::post('/custom-videos/edit-requests/{editRequestId}/status', [AdminCustomVideoController::class, 'updateEditRequestStatus'])->name('admin.custom-videos.edit-requests.update-status');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
    });
});
