<?php

use App\Http\Controllers\PublicSite\CalculatorController;
use App\Http\Controllers\PublicSite\ContactController;
use App\Http\Controllers\PublicSite\ContributorController;
use App\Http\Controllers\PublicSite\HomeController;
use App\Http\Controllers\PublicSite\InterpretationController;
use App\Http\Controllers\PublicSite\KnowledgeController;
use App\Http\Controllers\PublicSite\LaboratoryToolController;
use App\Http\Controllers\PublicSite\LegalController;
use App\Http\Controllers\PublicSite\LiveSearchController;
use App\Http\Controllers\PublicSite\ReferenceRangeController;
use App\Http\Controllers\PublicSite\SearchController;
use App\Http\Controllers\PublicSite\SeoController;
use App\Http\Controllers\PublicSite\SopController;
use App\Http\Controllers\Member\MessageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/recently-viewed/clear', [HomeController::class, 'clearRecentlyViewed'])
    ->middleware(['auth', 'active.user', 'member'])
    ->name('recently-viewed.clear');

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');

Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])->middleware(['honeypot', 'throttle:contact'])->name('contact.store');

Route::middleware(['auth', 'active.user', 'member'])->group(function () {
    Route::get('/account/messages', [MessageController::class, 'index'])->name('account.messages');
});

Route::get('/contributors/{profile_slug}', [ContributorController::class, 'show'])->name('contributors.show');

Route::get('/search', SearchController::class)
    ->middleware('throttle:search')
    ->name('public.search');

Route::get('/search/live', LiveSearchController::class)
    ->middleware('throttle:search-live')
    ->name('public.search.live');

Route::prefix('calculators')->name('public.calculators.')->group(function () {
    Route::get('/', [CalculatorController::class, 'index'])->name('index');
    Route::get('/{slug}', [CalculatorController::class, 'show'])->name('show');
});

Route::prefix('reference-ranges')->name('public.reference-ranges.')->group(function () {
    Route::get('/', [ReferenceRangeController::class, 'index'])->name('index');
    Route::get('/{slug}', [ReferenceRangeController::class, 'show'])->name('show');
});

Route::prefix('interpretation')->name('public.interpretation.')->group(function () {
    Route::get('/', [InterpretationController::class, 'index'])->name('index');
    Route::get('/{slug}', [InterpretationController::class, 'show'])->name('show');
});

Route::prefix('knowledge')->name('public.knowledge.')->group(function () {
    Route::get('/', [KnowledgeController::class, 'index'])->name('index');
    Route::get('/{slug}', [KnowledgeController::class, 'show'])->name('show');
});

Route::prefix('tools')->name('public.tools.')->group(function () {
    Route::get('/', [LaboratoryToolController::class, 'index'])->name('index');
    Route::get('/{slug}', [LaboratoryToolController::class, 'show'])->name('show');
});

Route::prefix('sops')->name('public.sops.')->group(function () {
    Route::get('/', [SopController::class, 'index'])->name('index');
    Route::get('/{slug}', [SopController::class, 'show'])->name('show');
});

Route::prefix('legal')->name('legal.')->group(function () {
    Route::get('/privacy-policy', [LegalController::class, 'privacyPolicy'])->name('privacy-policy');
    Route::get('/terms-of-service', [LegalController::class, 'termsOfService'])->name('terms-of-service');
    Route::get('/disclaimer', [LegalController::class, 'disclaimer'])->name('disclaimer');
    Route::get('/accessibility-statement', [LegalController::class, 'accessibilityStatement'])->name('accessibility-statement');
    Route::get('/cookie-policy', [LegalController::class, 'cookiePolicy'])->name('cookie-policy');
});
