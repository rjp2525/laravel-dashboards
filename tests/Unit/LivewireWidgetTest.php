<?php

use Livewire\Livewire;
use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\Livewire\DashboardWidget;
use Reno\Dashboard\Support\RefreshConfig;
use Reno\Dashboard\Support\WidgetData;
use Reno\Dashboard\Widgets\StatWidget;

beforeEach(function (): void {
    if (! class_exists(\Livewire\Livewire::class)) {
        $this->markTestSkipped('Livewire is not installed.');
    }
});

test('DashboardWidget mounts and loads data', function (): void {
    $widget = mock(StatWidget::class)->makePartial();
    $widget->shouldReceive('key')->andReturn('test-stat');
    $widget->shouldReceive('toArray')->andReturn([
        'key' => 'test-stat',
        'label' => 'Test Stat',
        'type' => 'stat',
    ]);

    $mockData = new WidgetData(value: 42);

    $manager = mock(DashboardManager::class);
    $manager->shouldReceive('getWidget')
        ->with('test-stat')
        ->andReturn($widget);
    $manager->shouldReceive('resolveWidgetData')
        ->andReturn($mockData);

    app()->instance(DashboardManager::class, $manager);

    Livewire::test(DashboardWidget::class, [
        'widgetKey' => 'test-stat',
        'dashboardSlug' => 'main',
    ])
        ->assertSet('widgetKey', 'test-stat')
        ->assertSet('dashboardSlug', 'main');
});

it('handleWidgetUpdate filters by widget_key', function (): void {
    $widget = mock(StatWidget::class)->makePartial();
    $widget->shouldReceive('key')->andReturn('my-widget');
    $widget->shouldReceive('toArray')->andReturn([
        'key' => 'my-widget',
        'label' => 'My Widget',
        'type' => 'stat',
    ]);

    $mockData = new WidgetData(value: 10);

    $manager = mock(DashboardManager::class);
    $manager->shouldReceive('getWidget')
        ->with('my-widget')
        ->andReturn($widget);
    $manager->shouldReceive('resolveWidgetData')
        ->andReturn($mockData);

    app()->instance(DashboardManager::class, $manager);

    $component = Livewire::test(DashboardWidget::class, [
        'widgetKey' => 'my-widget',
        'dashboardSlug' => 'main',
    ]);

    // Should ignore updates for other widgets
    $component->call('handleWidgetUpdate', 'other-widget', ['value' => 99]);
    $originalData = $component->get('widgetData');
    expect($originalData['value'])->toBe(10);

    // Should accept updates for matching widget
    $component->call('handleWidgetUpdate', 'my-widget', ['value' => 55]);
    expect($component->get('widgetData'))->toBe(['value' => 55]);
});

it('refreshInterval is null for manual strategy widgets', function (): void {
    $widget = mock(StatWidget::class)->makePartial();
    $widget->shouldReceive('key')->andReturn('manual-widget');
    $widget->shouldReceive('toArray')->andReturn([
        'key' => 'manual-widget',
        'label' => 'Manual',
        'type' => 'stat',
    ]);
    $widget->shouldReceive('refreshStrategy')
        ->andReturn(RefreshConfig::manual());

    $mockData = new WidgetData(value: 1);

    $manager = mock(DashboardManager::class);
    $manager->shouldReceive('getWidget')
        ->with('manual-widget')
        ->andReturn($widget);
    $manager->shouldReceive('resolveWidgetData')
        ->andReturn($mockData);

    app()->instance(DashboardManager::class, $manager);

    $component = Livewire::test(DashboardWidget::class, [
        'widgetKey' => 'manual-widget',
        'dashboardSlug' => 'main',
    ]);

    expect($component->instance()->refreshInterval)->toBeNull();
});

it('refreshInterval returns interval for poll strategy widgets', function (): void {
    $widget = mock(StatWidget::class)->makePartial();
    $widget->shouldReceive('key')->andReturn('poll-widget');
    $widget->shouldReceive('toArray')->andReturn([
        'key' => 'poll-widget',
        'label' => 'Poller',
        'type' => 'stat',
    ]);
    $widget->shouldReceive('refreshStrategy')
        ->andReturn(RefreshConfig::poll(30));

    $mockData = new WidgetData(value: 5);

    $manager = mock(DashboardManager::class);
    $manager->shouldReceive('getWidget')
        ->with('poll-widget')
        ->andReturn($widget);
    $manager->shouldReceive('resolveWidgetData')
        ->andReturn($mockData);

    app()->instance(DashboardManager::class, $manager);

    $component = Livewire::test(DashboardWidget::class, [
        'widgetKey' => 'poll-widget',
        'dashboardSlug' => 'main',
    ]);

    expect($component->instance()->refreshInterval)->toBe('30s');
});
