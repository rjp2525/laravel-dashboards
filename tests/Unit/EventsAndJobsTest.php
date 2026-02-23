<?php

use Illuminate\Broadcasting\Channel;
use Illuminate\Support\Facades\Event;
use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\Events\DashboardSaved;
use Reno\Dashboard\Events\PresetApplied;
use Reno\Dashboard\Events\WidgetDataUpdated;
use Reno\Dashboard\Jobs\BroadcastWidgetUpdate;
use Reno\Dashboard\Jobs\RefreshWidgetCache;
use Reno\Dashboard\Tests\Fixtures\TestStatWidget;

test('WidgetDataUpdated constructor sets properties', function (): void {
    $event = new WidgetDataUpdated('main', 'test-stat', ['value' => 100]);

    expect($event->dashboardSlug)->toBe('main')
        ->and($event->widgetKey)->toBe('test-stat')
        ->and($event->data)->toBe(['value' => 100]);
});

test('WidgetDataUpdated broadcastOn() returns Channel with correct prefix', function (): void {
    config()->set('dashboard.broadcasting.channel_prefix', 'dashboard');

    $event = new WidgetDataUpdated('main', 'test-stat', []);
    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(1)
        ->and($channels[0])->toBeInstanceOf(Channel::class)
        ->and($channels[0]->name)->toBe('dashboard.main');
});

test('WidgetDataUpdated broadcastAs() returns widget.updated', function (): void {
    $event = new WidgetDataUpdated('main', 'test-stat', []);

    expect($event->broadcastAs())->toBe('widget.updated');
});

test('WidgetDataUpdated broadcastWith() returns widget_key and data', function (): void {
    $data = ['value' => 42, 'change' => 5];
    $event = new WidgetDataUpdated('main', 'test-stat', $data);

    $payload = $event->broadcastWith();

    expect($payload)->toBe([
        'widget_key' => 'test-stat',
        'data' => $data,
    ]);
});

test('DashboardSaved constructor sets properties', function (): void {
    $event = new DashboardSaved('main', '123');

    expect($event->dashboardSlug)->toBe('main')
        ->and($event->userId)->toBe('123');
});

test('DashboardSaved broadcastOn() returns Channel', function (): void {
    config()->set('dashboard.broadcasting.channel_prefix', 'dashboard');

    $event = new DashboardSaved('analytics', '456');
    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(1)
        ->and($channels[0])->toBeInstanceOf(Channel::class)
        ->and($channels[0]->name)->toBe('dashboard.analytics');
});

test('DashboardSaved broadcastAs() returns dashboard.saved', function (): void {
    $event = new DashboardSaved('main', '123');

    expect($event->broadcastAs())->toBe('dashboard.saved');
});

test('PresetApplied constructor sets properties', function (): void {
    $event = new PresetApplied('main', 'preset-1', 'user-99');

    expect($event->dashboardSlug)->toBe('main')
        ->and($event->presetId)->toBe('preset-1')
        ->and($event->userId)->toBe('user-99');
});

test('PresetApplied broadcastOn() returns Channel', function (): void {
    config()->set('dashboard.broadcasting.channel_prefix', 'dashboard');

    $event = new PresetApplied('sales', 'preset-5', 'user-1');
    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(1)
        ->and($channels[0])->toBeInstanceOf(Channel::class)
        ->and($channels[0]->name)->toBe('dashboard.sales');
});

test('PresetApplied broadcastAs() returns preset.applied', function (): void {
    $event = new PresetApplied('main', 'preset-1', 'user-1');

    expect($event->broadcastAs())->toBe('preset.applied');
});

it('RefreshWidgetCache can be dispatched', function (): void {
    $job = new RefreshWidgetCache('test-stat', 'main', '30d');

    expect($job->widgetKey)->toBe('test-stat')
        ->and($job->dashboardSlug)->toBe('main')
        ->and($job->period)->toBe('30d');
});

test('RefreshWidgetCache handle() with registered widget resolves data', function (): void {
    $manager = app(DashboardManager::class);
    $manager->register(TestStatWidget::class);

    // Disable broadcasting so handle() does not try to dispatch broadcast event
    config()->set('dashboard.broadcasting.enabled', false);

    $job = new RefreshWidgetCache('test-stat', 'main', '30d');
    $job->handle($manager);

    // If we get here without exception, the widget was resolved successfully
    // Verify the widget still exists and is accessible
    $widget = $manager->getWidget('test-stat');
    expect($widget)->not->toBeNull();
});

it('BroadcastWidgetUpdate can be dispatched', function (): void {
    $job = new BroadcastWidgetUpdate('main', 'test-stat', ['value' => 42]);

    expect($job->dashboardSlug)->toBe('main')
        ->and($job->widgetKey)->toBe('test-stat')
        ->and($job->data)->toBe(['value' => 42]);
});

test('BroadcastWidgetUpdate handle() dispatches WidgetDataUpdated event', function (): void {
    Event::fake([WidgetDataUpdated::class]);

    $job = new BroadcastWidgetUpdate('main', 'test-stat', ['value' => 55]);
    $job->handle();

    Event::assertDispatched(WidgetDataUpdated::class, function (WidgetDataUpdated $event): bool {
        return $event->dashboardSlug === 'main'
            && $event->widgetKey === 'test-stat'
            && $event->data === ['value' => 55];
    });
});
