<?php

use App\Http\Controllers\Auth\AdminInvitationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MemberForgotPasswordController;
use App\Http\Controllers\Auth\MemberResetPasswordController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('auth.')->middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('honeypot')->name('login.attempt');
});

Route::prefix('admin/invitation')->name('admin.invitation.')->middleware('guest')->group(function () {
    Route::get('/{token}', [AdminInvitationController::class, 'show'])->name('show');
    Route::post('/{token}', [AdminInvitationController::class, 'accept'])->middleware('throttle:6,1')->name('accept');
});

Route::post('/admin/logout', [LoginController::class, 'logout'])->middleware('auth')->name('auth.logout');

Route::name('auth.')->middleware('guest')->group(function () {
    Route::get('/forgot-password', [MemberForgotPasswordController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [MemberForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [MemberResetPasswordController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::put('/reset-password', [MemberResetPasswordController::class, 'resetPassword'])->name('password.update');
});
