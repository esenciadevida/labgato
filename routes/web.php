<?php

use App\Http\Controllers\PublicSite\CalculatorController;
use App\Http\Controllers\PublicSite\HomeController;
use App\Http\Controllers\PublicSite\LegalController;
use App\Http\Controllers\PublicSite\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/search', SearchController::class)
    ->middleware('throttle:search')
    ->name('public.search');

Route::prefix('calculators')->name('public.calculators.')->group(function () {
    Route::get('/', [CalculatorController::class, 'index'])->name('index');
    Route::get('/{slug}', [CalculatorController::class, 'show'])->name('show');
});

Route::prefix('legal')->name('legal.')->group(function () {
    Route::get('/privacy-policy', [LegalController::class, 'privacyPolicy'])->name('privacy-policy');
    Route::get('/terms-of-service', [LegalController::class, 'termsOfService'])->name('terms-of-service');
    Route::get('/disclaimer', [LegalController::class, 'disclaimer'])->name('disclaimer');
    Route::get('/accessibility-statement', [LegalController::class, 'accessibilityStatement'])->name('accessibility-statement');
    Route::get('/cookie-policy', [LegalController::class, 'cookiePolicy'])->name('cookie-policy');
});
