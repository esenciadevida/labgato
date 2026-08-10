<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'ensure_user_is_active'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
});
