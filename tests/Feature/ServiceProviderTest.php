<?php

use Illuminate\Support\Facades\Route;
use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\Facades\Dashboard;

it('registers DashboardManager as singleton', function (): void {
    $instance1 = app(DashboardManager::class);
    $instance2 = app(DashboardManager::class);

    expect($instance1)->toBe($instance2);
    expect($instance1)->toBeInstanceOf(DashboardManager::class);
});

it('Dashboard facade resolves to DashboardManager', function (): void {
    $resolved = Dashboard::getFacadeRoot();

    expect($resolved)->toBeInstanceOf(DashboardManager::class);
});

it('config is merged', function (): void {
    expect(config('dashboard'))->toBeArray();
    expect(config('dashboard.routing'))->toBeArray();
    expect(config('dashboard.routing.prefix'))->toBe('dashboard');
    expect(config('dashboard.grid'))->toBeArray();
    expect(config('dashboard.cache'))->toBeArray();
    expect(config('dashboard.periods'))->toBeArray();
});

it('web routes are registered', function (): void {
    $route = Route::getRoutes()->getByName('dashboard.show');

    expect($route)->not->toBeNull();
    expect($route->uri())->toContain('dashboard');
});

it('API routes are registered', function (): void {
    $widgetDataRoute = Route::getRoutes()->getByName('dashboard.widget.data');
    $batchRoute = Route::getRoutes()->getByName('dashboard.widget.batch');
    $exportRoute = Route::getRoutes()->getByName('dashboard.widget.export');
    $layoutShowRoute = Route::getRoutes()->getByName('dashboard.layout.show');
    $layoutUpdateRoute = Route::getRoutes()->getByName('dashboard.layout.update');
    $presetsIndexRoute = Route::getRoutes()->getByName('dashboard.presets.index');
    $presetsStoreRoute = Route::getRoutes()->getByName('dashboard.presets.store');
    $presetsShowRoute = Route::getRoutes()->getByName('dashboard.presets.show');
    $presetsUpdateRoute = Route::getRoutes()->getByName('dashboard.presets.update');
    $presetsDestroyRoute = Route::getRoutes()->getByName('dashboard.presets.destroy');
    $presetsApplyRoute = Route::getRoutes()->getByName('dashboard.presets.apply');

    expect($widgetDataRoute)->not->toBeNull();
    expect($batchRoute)->not->toBeNull();
    expect($exportRoute)->not->toBeNull();
    expect($layoutShowRoute)->not->toBeNull();
    expect($layoutUpdateRoute)->not->toBeNull();
    expect($presetsIndexRoute)->not->toBeNull();
    expect($presetsStoreRoute)->not->toBeNull();
    expect($presetsShowRoute)->not->toBeNull();
    expect($presetsUpdateRoute)->not->toBeNull();
    expect($presetsDestroyRoute)->not->toBeNull();
    expect($presetsApplyRoute)->not->toBeNull();
});
