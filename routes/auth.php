<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
        Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetPasswordForm'])->name('password.reset');
        Route::put('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
    });

    Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');
});
