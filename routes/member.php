<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\MemberLoginController;
use App\Http\Controllers\Auth\MemberRegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Member\ContributionController;
use App\Http\Controllers\Member\MemberController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RecentlyViewedController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [MemberRegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [MemberRegisterController::class, 'register'])->middleware('honeypot')->name('register');
    Route::get('/login', [MemberLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [MemberLoginController::class, 'login'])->middleware('honeypot')->name('login');
    Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
});

Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

Route::post('/logout', [MemberLoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.send');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    Route::get('/recently-viewed', [RecentlyViewedController::class, 'index'])->name('recently-viewed.index');
    Route::post('/recently-viewed', [RecentlyViewedController::class, 'store'])->name('recently-viewed.store');
    Route::delete('/recently-viewed/{id}', [RecentlyViewedController::class, 'destroy'])->name('recently-viewed.destroy');
    Route::post('/recently-viewed/clear', [RecentlyViewedController::class, 'clear'])->name('recently-viewed.clear');
});

Route::prefix('account')->name('account.')->middleware(['member', 'active.user'])->group(function () {
    Route::get('/dashboard', [MemberController::class, 'show'])->name('dashboard');
    Route::get('/profile', [MemberController::class, 'profile'])->name('profile');
    Route::put('/profile', [MemberController::class, 'updateProfile'])->name('profile.update');

    Route::prefix('contributions')->name('contributions.')->group(function () {
        Route::get('/', [ContributionController::class, 'index'])->name('index');
        Route::get('/create', [ContributionController::class, 'create'])->name('create');
        Route::post('/{type}', [ContributionController::class, 'store'])->name('store');
        Route::get('/{type}/{id}', [ContributionController::class, 'show'])->name('show');
        Route::get('/{type}/{id}/edit', [ContributionController::class, 'edit'])->name('edit');
        Route::put('/{type}/{id}', [ContributionController::class, 'update'])->name('update');
        Route::delete('/{type}/{id}', [ContributionController::class, 'destroy'])->name('destroy');
        Route::post('/{type}/{id}/submit', [ContributionController::class, 'submit'])->name('submit');
        Route::post('/{type}/{id}/withdraw', [ContributionController::class, 'withdraw'])->name('withdraw');
        Route::post('/{type}/{id}/resubmit', [ContributionController::class, 'resubmit'])->name('resubmit');
    });
});
