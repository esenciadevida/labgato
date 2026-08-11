<?php

use App\Http\Controllers\Admin\CalculatorController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TagController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('dashboard');

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
    });

    Route::middleware('permission:calculators.delete')->group(function () {
        Route::delete('/calculators/{calculator}', [CalculatorController::class, 'destroy'])->name('calculators.destroy');
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
});
