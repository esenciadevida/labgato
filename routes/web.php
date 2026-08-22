<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\ContentImageController;
use App\Http\Controllers\ContentPreviewController;
use App\Http\Controllers\Member\MessageController;
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
use App\Mail\ContactReplyMail;
use App\Mail\ContributionStatusMail;
use App\Mail\ResetPasswordEmail;
use App\Mail\SignInNotification;
use App\Mail\WelcomeEmail;
use App\Models\ContactMessage;
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

Route::post('/content-images', [ContentImageController::class, 'store'])
    ->middleware(['auth', 'active.user', 'throttle:60,1'])
    ->name('content-images.store');

Route::post('/content-preview', ContentPreviewController::class)
    ->middleware(['auth', 'active.user', 'throttle:120,1'])
    ->name('content.preview');

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

if (! app()->isProduction()) {
    Route::middleware(['auth', 'active.user', 'member'])
        ->prefix('dev')
        ->name('dev.')
        ->group(function () {
            Route::get('/email-preview/{template}', function (string $template) {
                $user = auth()->user();

                $previews = [
                    'welcome' => fn () => new WelcomeEmail($user),
                    'sign-in' => fn () => new SignInNotification(
                        $user,
                        '127.0.0.1',
                        'Chrome 126 on macOS',
                        now()->format('M j, Y g:i A'),
                    ),
                    'reset' => fn () => new ResetPasswordEmail(
                        $user,
                        url('/reset-password/sample-token?email='.urlencode($user->email)),
                        '127.0.0.1',
                        60,
                    ),
                    'reply' => fn () => new ContactReplyMail(
                        (new ContactMessage)->forceFill([
                            'id' => 1,
                            'name' => $user->name,
                            'subject' => 'Question about reference ranges',
                            'message' => 'Hello, I could not find the reference range for serum osmolality. Could you point me in the right direction?',
                        ]),
                        'Thanks for reaching out! You can find it under Reference Ranges > Clinical Chemistry. Let us know if you need anything else.',
                        'Admin Team',
                    ),
                    'approved' => fn () => new ContributionStatusMail(
                        $user,
                        'SOP',
                        'Test SOP',
                        'approved',
                        ctaUrl: url('/'),
                        totalContributions: 12,
                    ),
                    'rejected' => fn () => new ContributionStatusMail(
                        $user,
                        'SOP',
                        'Test SOP',
                        'rejected',
                        reason: 'Needs more detail in the procedure section before it can be published.',
                        ctaUrl: url('/account/contributions'),
                    ),
                ];

                $factory = $previews[$template] ?? null;

                abort_if($factory === null, 404);

                return $factory();
            })->name('email-preview');
        });
}
