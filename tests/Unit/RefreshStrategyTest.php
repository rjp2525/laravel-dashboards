<?php

use Reno\Dashboard\Enums\RefreshStrategy;
use Reno\Dashboard\Support\RefreshConfig;
use Reno\Dashboard\Widgets\Traits\HasRefreshInterval;

test('RefreshConfig::inertia() creates config with INERTIA strategy and default interval', function (): void {
    $config = RefreshConfig::inertia();

    expect($config->strategy)->toBe(RefreshStrategy::INERTIA);
    expect($config->interval)->toBe(60);
});

test('RefreshConfig::inertia() accepts custom interval', function (): void {
    $config = RefreshConfig::inertia(30);

    expect($config->strategy)->toBe(RefreshStrategy::INERTIA);
    expect($config->interval)->toBe(30);
});

test('RefreshConfig::inertia() toArray returns correct values', function (): void {
    $config = RefreshConfig::inertia(45);

    expect($config->toArray())->toBe([
        'strategy' => 'inertia',
        'interval' => 45,
    ]);
});

test('HasRefreshInterval::inertiaPolling() sets strategy and interval', function (): void {
    $widget = new class
    {
        use HasRefreshInterval;
    };

    $result = $widget->inertiaPolling(90);

    expect($result)->toBe($widget);

    $config = $widget->refreshStrategy();
    expect($config->strategy)->toBe(RefreshStrategy::INERTIA);
    expect($config->interval)->toBe(90);
});

test('HasRefreshInterval::inertiaPolling() uses default interval of 60', function (): void {
    $widget = new class
    {
        use HasRefreshInterval;
    };

    $widget->inertiaPolling();

    $config = $widget->refreshStrategy();
    expect($config->strategy)->toBe(RefreshStrategy::INERTIA);
    expect($config->interval)->toBe(60);
});
