<?php

use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\Models\Dashboard;
use Reno\Dashboard\Models\DashboardPreset;
use Reno\Dashboard\Tests\Fixtures\TestStatWidget;

it('dashboard:install runs and publishes config', function (): void {
    $this->artisan('dashboard:install')
        ->expectsConfirmation('Run database migrations?', 'no')
        ->expectsConfirmation('Create a sample dashboard?', 'no')
        ->assertSuccessful();
});

it('dashboard:install creates sample dashboard when confirmed', function (): void {
    $this->artisan('dashboard:install')
        ->expectsConfirmation('Run database migrations?', 'no')
        ->expectsConfirmation('Create a sample dashboard?', 'yes')
        ->assertSuccessful();

    expect(Dashboard::where('slug', 'main')->exists())->toBeTrue();
});

it('dashboard:widget creates a widget file', function (): void {
    $this->artisan('dashboard:widget', ['name' => 'RevenueWidget'])
        ->assertSuccessful();
});

it('dashboard:warm runs with no dashboards and outputs warning', function (): void {
    $this->artisan('dashboard:warm')
        ->expectsOutput('No dashboards found.')
        ->assertSuccessful();
});

it('dashboard:warm runs with a dashboard and registered widgets', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    $manager = app(DashboardManager::class);
    $manager->register(TestStatWidget::class);

    $dashboard->widgets()->create([
        'widget_class' => TestStatWidget::class,
        'widget_key' => 'test-stat',
        'label' => 'Test Stat',
        'type' => 'stat',
        'is_active' => true,
    ]);

    $this->artisan('dashboard:warm')
        ->assertSuccessful();
});

it('dashboard:preset list works', function (): void {
    $this->artisan('dashboard:preset', ['action' => 'list'])
        ->expectsOutput('No presets found.')
        ->assertSuccessful();
});

it('dashboard:preset list shows presets when they exist', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'Default Layout',
        'layout' => [],
        'is_system' => true,
    ]);

    $this->artisan('dashboard:preset', ['action' => 'list'])
        ->assertSuccessful();
});

it('dashboard:preset create works', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    $this->artisan('dashboard:preset', [
        'action' => 'create',
        '--dashboard' => 'main',
        '--name' => 'New Preset',
    ])
        ->assertSuccessful();

    expect(DashboardPreset::where('name', 'New Preset')->exists())->toBeTrue();
});

it('dashboard:preset delete works', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'To Delete',
        'layout' => [],
        'is_system' => false,
    ]);

    $this->artisan('dashboard:preset', [
        'action' => 'delete',
        '--name' => 'To Delete',
    ])
        ->assertSuccessful();

    expect(DashboardPreset::where('name', 'To Delete')->exists())->toBeFalse();
});

it('dashboard:preset with invalid action fails', function (): void {
    $this->artisan('dashboard:preset', ['action' => 'invalid'])
        ->assertFailed();
});
