<?php

use App\Http\Controllers\Admin\AdministratorController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BulkActionController;
use App\Http\Controllers\Admin\CalculatorController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ContributionReviewController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InterpretationController;
use App\Http\Controllers\Admin\KnowledgeController;
use App\Http\Controllers\Admin\LaboratoryToolController;
use App\Http\Controllers\Admin\PopupController;
use App\Http\Controllers\Admin\ReferenceRangeController;
use App\Http\Controllers\Admin\ReferenceRangeImportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SopController;
use App\Http\Controllers\Admin\SopVersionController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'active.user', 'admin.inactivity'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('dashboard');

    Route::post('/bulk/{type}', [BulkActionController::class, 'process'])->name('bulk');

    Route::middleware('permission:calculators.view')->group(function () {
        Route::get('/calculators', [CalculatorController::class, 'index'])->name('calculators.index');
    });

    Route::middleware('permission:calculators.create')->group(function () {
        Route::get('/calculators/create', [CalculatorController::class, 'create'])->name('calculators.create');
        Route::post('/calculators', [CalculatorController::class, 'store'])->name('calculators.store');
    });

    Route::middleware('permission:calculators.edit')->group(function () {
        Route::get('/calculators/{calculator}/edit', [CalculatorController::class, 'edit'])->name('calculators.edit');
        Route::put('/calculators/{calculator}', [CalculatorController::class, 'update'])->name('calculators.update');
        Route::patch('/calculators/{calculator}/publish', [CalculatorController::class, 'publish'])->name('calculators.publish');
        Route::patch('/calculators/{calculator}/archive', [CalculatorController::class, 'archive'])->name('calculators.archive');
    });

    Route::middleware('permission:calculators.delete')->group(function () {
        Route::delete('/calculators/{calculator}', [CalculatorController::class, 'destroy'])->name('calculators.destroy');
    });

    Route::middleware('permission:reference-ranges.view')->group(function () {
        Route::get('/reference-ranges', [ReferenceRangeController::class, 'index'])->name('reference-ranges.index');
    });

    Route::middleware('permission:reference-ranges.create')->group(function () {
        Route::get('/reference-ranges/create', [ReferenceRangeController::class, 'create'])->name('reference-ranges.create');
        Route::post('/reference-ranges', [ReferenceRangeController::class, 'store'])->name('reference-ranges.store');
    });

    Route::middleware('permission:reference-ranges.edit')->group(function () {
        Route::get('/reference-ranges/{referenceRange}/edit', [ReferenceRangeController::class, 'edit'])->name('reference-ranges.edit');
        Route::put('/reference-ranges/{referenceRange}', [ReferenceRangeController::class, 'update'])->name('reference-ranges.update');
        Route::patch('/reference-ranges/{referenceRange}/publish', [ReferenceRangeController::class, 'publish'])->name('reference-ranges.publish');
        Route::patch('/reference-ranges/{referenceRange}/archive', [ReferenceRangeController::class, 'archive'])->name('reference-ranges.archive');
    });

    Route::middleware('permission:reference-ranges.delete')->group(function () {
        Route::delete('/reference-ranges/{referenceRange}', [ReferenceRangeController::class, 'destroy'])->name('reference-ranges.destroy');
    });

    Route::middleware('permission:reference-ranges.import')->group(function () {
        Route::get('/reference-ranges/import', [ReferenceRangeImportController::class, 'showImportForm'])->name('reference-ranges.import');
        Route::get('/reference-ranges/import/template', [ReferenceRangeImportController::class, 'template'])->name('reference-ranges.import.template');
        Route::post('/reference-ranges/import/preview', [ReferenceRangeImportController::class, 'preview'])->name('reference-ranges.import.preview');
        Route::post('/reference-ranges/import', [ReferenceRangeImportController::class, 'import'])->name('reference-ranges.import.confirm');
    });

    Route::middleware('permission:interpretation.view')->group(function () {
        Route::get('/interpretation', [InterpretationController::class, 'index'])->name('interpretation.index');
    });

    Route::middleware('permission:interpretation.create')->group(function () {
        Route::get('/interpretation/create', [InterpretationController::class, 'create'])->name('interpretation.create');
        Route::post('/interpretation', [InterpretationController::class, 'store'])->name('interpretation.store');
    });

    Route::middleware('permission:interpretation.edit')->group(function () {
        Route::get('/interpretation/{interpretation}/edit', [InterpretationController::class, 'edit'])->name('interpretation.edit');
        Route::put('/interpretation/{interpretation}', [InterpretationController::class, 'update'])->name('interpretation.update');
        Route::patch('/interpretation/{interpretation}/publish', [InterpretationController::class, 'publish'])->name('interpretation.publish');
        Route::patch('/interpretation/{interpretation}/archive', [InterpretationController::class, 'archive'])->name('interpretation.archive');
    });

    Route::middleware('permission:interpretation.delete')->group(function () {
        Route::delete('/interpretation/{interpretation}', [InterpretationController::class, 'destroy'])->name('interpretation.destroy');
    });

    Route::middleware('permission:knowledge.view')->group(function () {
        Route::get('/knowledge', [KnowledgeController::class, 'index'])->name('knowledge.index');
    });

    Route::middleware('permission:knowledge.create')->group(function () {
        Route::get('/knowledge/create', [KnowledgeController::class, 'create'])->name('knowledge.create');
        Route::post('/knowledge', [KnowledgeController::class, 'store'])->name('knowledge.store');
    });

    Route::middleware('permission:knowledge.edit')->group(function () {
        Route::get('/knowledge/{knowledge}/edit', [KnowledgeController::class, 'edit'])->name('knowledge.edit');
        Route::put('/knowledge/{knowledge}', [KnowledgeController::class, 'update'])->name('knowledge.update');
        Route::patch('/knowledge/{knowledge}/publish', [KnowledgeController::class, 'publish'])->name('knowledge.publish');
        Route::patch('/knowledge/{knowledge}/archive', [KnowledgeController::class, 'archive'])->name('knowledge.archive');
    });

    Route::middleware('permission:knowledge.delete')->group(function () {
        Route::delete('/knowledge/{knowledge}', [KnowledgeController::class, 'destroy'])->name('knowledge.destroy');
    });

    Route::middleware('permission:tools.view')->group(function () {
        Route::get('/tools', [LaboratoryToolController::class, 'index'])->name('tools.index');
    });

    Route::middleware('permission:tools.create')->group(function () {
        Route::get('/tools/create', [LaboratoryToolController::class, 'create'])->name('tools.create');
        Route::post('/tools', [LaboratoryToolController::class, 'store'])->name('tools.store');
    });

    Route::middleware('permission:tools.edit')->group(function () {
        Route::get('/tools/{tool}/edit', [LaboratoryToolController::class, 'edit'])->name('tools.edit');
        Route::put('/tools/{tool}', [LaboratoryToolController::class, 'update'])->name('tools.update');
        Route::patch('/tools/{tool}/publish', [LaboratoryToolController::class, 'publish'])->name('tools.publish');
        Route::patch('/tools/{tool}/archive', [LaboratoryToolController::class, 'archive'])->name('tools.archive');
    });

    Route::middleware('permission:tools.delete')->group(function () {
        Route::delete('/tools/{tool}', [LaboratoryToolController::class, 'destroy'])->name('tools.destroy');
    });

    Route::middleware('permission:sops.view')->group(function () {
        Route::get('/sops', [SopController::class, 'index'])->name('sops.index');
        Route::get('/sops/{sop}/versions', [SopVersionController::class, 'index'])->name('sops.versions.index');
        Route::get('/sops/{sop}/versions/{version}', [SopVersionController::class, 'show'])->name('sops.versions.show');
    });

    Route::middleware('permission:contributions.review')->group(function () {
        Route::get('/contributions', [ContributionReviewController::class, 'index'])->name('contributions.index');
        Route::get('/contributions/{type}/{id}/review', [ContributionReviewController::class, 'show'])->name('contributions.review');
        Route::post('/contributions/{type}/{id}/approve', [ContributionReviewController::class, 'approve'])->name('contributions.approve');
        Route::post('/contributions/{type}/{id}/reject', [ContributionReviewController::class, 'reject'])->name('contributions.reject');
    });

    Route::middleware('permission:sops.create')->group(function () {
        Route::get('/sops/create', [SopController::class, 'create'])->name('sops.create');
        Route::post('/sops', [SopController::class, 'store'])->name('sops.store');
    });

    Route::middleware('permission:sops.edit')->group(function () {
        Route::get('/sops/{sop}/edit', [SopController::class, 'edit'])->name('sops.edit');
        Route::put('/sops/{sop}', [SopController::class, 'update'])->name('sops.update');
        Route::patch('/sops/{sop}/publish', [SopController::class, 'publish'])->name('sops.publish');
        Route::patch('/sops/{sop}/archive', [SopController::class, 'archive'])->name('sops.archive');
        Route::post('/sops/{sop}/versions/{version}/restore', [SopVersionController::class, 'restore'])->name('sops.versions.restore');
    });

    Route::middleware('permission:sops.delete')->group(function () {
        Route::delete('/sops/{sop}', [SopController::class, 'destroy'])->name('sops.destroy');
    });

    Route::middleware('permission:taxonomy.view')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
    });

    Route::middleware('permission:taxonomy.manage')->group(function () {
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/tags/create', [TagController::class, 'create'])->name('tags.create');
        Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
        Route::get('/tags/{tag}/edit', [TagController::class, 'edit'])->name('tags.edit');
        Route::put('/tags/{tag}', [TagController::class, 'update'])->name('tags.update');
        Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');
    });

    Route::middleware('permission:users.view')->group(function () {
        Route::get('/administrators', [AdministratorController::class, 'index'])->name('administrators.index');
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    });

    Route::middleware('permission:users.create')->group(function () {
        Route::get('/administrators/create', [AdministratorController::class, 'create'])->name('administrators.create');
        Route::post('/administrators', [AdministratorController::class, 'store'])->name('administrators.store');
        Route::post('/invitations/{invitation}/resend', [AdministratorController::class, 'resend'])->name('administrators.invitations.resend');
        Route::delete('/invitations/{invitation}', [AdministratorController::class, 'revoke'])->name('administrators.invitations.revoke');
    });

    Route::middleware('permission:users.edit')->group(function () {
        Route::get('/administrators/{administrator}/edit', [AdministratorController::class, 'edit'])->name('administrators.edit');
        Route::put('/administrators/{administrator}', [AdministratorController::class, 'update'])->name('administrators.update');
        Route::post('/administrators/{administrator}/toggle-active', [AdministratorController::class, 'toggleActive'])->name('administrators.toggle-active');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::post('/users/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])->name('users.toggle-active');
    });

    Route::middleware('permission:users.delete')->group(function () {
        Route::delete('/administrators/{administrator}', [AdministratorController::class, 'destroy'])->name('administrators.destroy');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('permission:roles.view')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    });

    Route::middleware('permission:roles.manage')->group(function () {
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    Route::middleware('permission:settings.view')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    });

    Route::middleware('permission:settings.manage')->group(function () {
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    Route::middleware('permission:audit-logs.view')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    });

    Route::middleware('permission:popups.manage')->prefix('popups')->name('popups.')->group(function () {
        Route::get('/', [PopupController::class, 'index'])->name('index');
        Route::get('/create', [PopupController::class, 'create'])->name('create');
        Route::post('/', [PopupController::class, 'store'])->name('store');
        Route::get('/{popup}/edit', [PopupController::class, 'edit'])->name('edit');
        Route::put('/{popup}', [PopupController::class, 'update'])->name('update');
        Route::delete('/{popup}', [PopupController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('permission:announcements.manage')->prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index'])->name('index');
        Route::get('/create', [AnnouncementController::class, 'create'])->name('create');
        Route::post('/', [AnnouncementController::class, 'store'])->name('store');
        Route::get('/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('edit');
        Route::put('/{announcement}', [AnnouncementController::class, 'update'])->name('update');
        Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('permission:contact-messages.view')->prefix('contact-messages')->name('contact-messages.')->group(function () {
        Route::get('/', [ContactMessageController::class, 'index'])->name('index');
        Route::get('/{message}', [ContactMessageController::class, 'show'])->name('show');
        Route::post('/{message}/reply', [ContactMessageController::class, 'reply'])->middleware('permission:contact-messages.reply')->name('reply');
    });
});
