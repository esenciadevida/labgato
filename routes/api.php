<?php

use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->middleware(['throttle:api-public'])->group(function () {
    Route::get('/health', [HealthController::class, 'index'])->name('health');
});
