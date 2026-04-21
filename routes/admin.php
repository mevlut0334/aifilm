<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:admin')->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    });
});

Route::get('/admin/login', function () {
    return view('admin.login');
})->name('admin.login');
