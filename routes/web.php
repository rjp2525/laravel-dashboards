<?php

use Illuminate\Support\Facades\Route;
use Reno\Dashboard\Http\Controllers\DashboardController;

Route::group([
    'prefix' => config('dashboard.routing.prefix', 'dashboard'),
    'middleware' => config('dashboard.routing.middleware', ['web', 'auth']),
    'domain' => config('dashboard.routing.domain'),
], function (): void {
    Route::get('/{slug?}', [DashboardController::class, 'show'])->name('dashboard.show');
});
