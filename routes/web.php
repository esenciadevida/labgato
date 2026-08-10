<?php

use App\Http\Controllers\PublicSite\HomeController;
use App\Http\Controllers\PublicSite\LegalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('legal')->name('legal.')->group(function () {
    Route::get('/privacy-policy', [LegalController::class, 'privacyPolicy'])->name('privacy-policy');
    Route::get('/terms-of-service', [LegalController::class, 'termsOfService'])->name('terms-of-service');
    Route::get('/disclaimer', [LegalController::class, 'disclaimer'])->name('disclaimer');
    Route::get('/accessibility-statement', [LegalController::class, 'accessibilityStatement'])->name('accessibility-statement');
    Route::get('/cookie-policy', [LegalController::class, 'cookiePolicy'])->name('cookie-policy');
});
