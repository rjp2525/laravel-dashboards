<?php

use Illuminate\Support\Facades\Route;
use Reno\Dashboard\Http\Controllers\BatchWidgetDataController;
use Reno\Dashboard\Http\Controllers\ExportController;
use Reno\Dashboard\Http\Controllers\LayoutController;
use Reno\Dashboard\Http\Controllers\PresetController;
use Reno\Dashboard\Http\Controllers\WidgetDataController;

Route::group([
    'prefix' => config('dashboard.routing.api_prefix', 'api/dashboard'),
    'middleware' => config('dashboard.routing.api_middleware', ['api', 'auth']),
    'domain' => config('dashboard.routing.domain'),
], function (): void {
    Route::get('/widgets/{key}/data', [WidgetDataController::class, 'show'])->name('dashboard.widget.data');
    Route::post('/widgets/batch', BatchWidgetDataController::class)->name('dashboard.widget.batch');
    Route::get('/widgets/{key}/export', ExportController::class)->name('dashboard.widget.export');

    Route::get('/{slug}/layout', [LayoutController::class, 'show'])->name('dashboard.layout.show');
    Route::put('/{slug}/layout', [LayoutController::class, 'update'])->name('dashboard.layout.update');

    Route::get('/{slug}/presets', [PresetController::class, 'index'])->name('dashboard.presets.index');
    Route::post('/{slug}/presets', [PresetController::class, 'store'])->name('dashboard.presets.store');
    Route::get('/{slug}/presets/{presetId}', [PresetController::class, 'show'])->name('dashboard.presets.show');
    Route::put('/{slug}/presets/{presetId}', [PresetController::class, 'update'])->name('dashboard.presets.update');
    Route::delete('/{slug}/presets/{presetId}', [PresetController::class, 'destroy'])->name('dashboard.presets.destroy');
    Route::post('/{slug}/presets/{presetId}/apply', [PresetController::class, 'apply'])->name('dashboard.presets.apply');
});
