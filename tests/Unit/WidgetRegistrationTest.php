<?php

use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Tests\Fixtures\TestChartWidget;
use Reno\Dashboard\Tests\Fixtures\TestStatWidget;

it('can register a widget class', function (): void {
    $manager = app(DashboardManager::class);
    $manager->register(TestStatWidget::class);

    expect($manager->registered())->toHaveKey('test-stat');
    expect($manager->getWidget('test-stat'))->toBeInstanceOf(TestStatWidget::class);
});

it('can register multiple widgets', function (): void {
    $manager = app(DashboardManager::class);
    $manager->register(TestStatWidget::class);
    $manager->register(TestChartWidget::class);

    expect($manager->registered())->toHaveCount(2);
    expect($manager->getWidget('test-stat'))->toBeInstanceOf(TestStatWidget::class);
    expect($manager->getWidget('test-chart'))->toBeInstanceOf(TestChartWidget::class);
});

it('returns null for unregistered widget', function (): void {
    $manager = app(DashboardManager::class);

    expect($manager->getWidget('nonexistent'))->toBeNull();
});

it('can register inline widgets via builder', function (): void {
    $manager = app(DashboardManager::class);

    $manager->widget('inline-stat')
        ->label('Inline Stat')
        ->type(WidgetType::STAT)
        ->using(fn (): int => 42)
        ->register();

    expect($manager->getWidget('inline-stat'))->not->toBeNull();
    expect($manager->getWidget('inline-stat')->label())->toBe('Inline Stat');
});

it('throws when registering invalid class', function (): void {
    $manager = app(DashboardManager::class);
    $manager->register(stdClass::class);
})->throws(InvalidArgumentException::class);
