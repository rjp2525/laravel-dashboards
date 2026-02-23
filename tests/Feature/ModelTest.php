<?php

use Reno\Dashboard\Enums\WidgetType;
use Reno\Dashboard\Models\Dashboard;
use Reno\Dashboard\Models\DashboardPreset;
use Reno\Dashboard\Models\DashboardWidget;
use Reno\Dashboard\Models\UserDashboard;
use Reno\Dashboard\Tests\Fixtures\TestUser;

beforeEach(function (): void {
    $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table): void {
        $table->id();
        $table->string('name')->default('');
        $table->string('email')->default('');
        $table->timestamps();
    });
});

// --- Dashboard model ---

it('dashboard hasMany widgets', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    DashboardWidget::create([
        'dashboard_id' => $dashboard->id,
        'widget_class' => 'App\\Widgets\\StatWidget',
        'widget_key' => 'stat-1',
        'label' => 'Stat One',
        'type' => 'stat',
        'is_active' => true,
    ]);

    DashboardWidget::create([
        'dashboard_id' => $dashboard->id,
        'widget_class' => 'App\\Widgets\\ChartWidget',
        'widget_key' => 'chart-1',
        'label' => 'Chart One',
        'type' => 'line',
        'is_active' => true,
    ]);

    expect($dashboard->widgets)->toHaveCount(2);
    expect($dashboard->widgets->first())->toBeInstanceOf(DashboardWidget::class);
});

it('dashboard hasMany presets', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'Preset One',
        'layout' => [],
        'is_system' => false,
    ]);

    expect($dashboard->presets)->toHaveCount(1);
    expect($dashboard->presets->first())->toBeInstanceOf(DashboardPreset::class);
});

it('dashboard hasMany userDashboards', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    UserDashboard::create([
        'user_type' => get_class($user),
        'user_id' => $user->id,
        'dashboard_id' => $dashboard->id,
        'layout' => [],
    ]);

    expect($dashboard->userDashboards)->toHaveCount(1);
    expect($dashboard->userDashboards->first())->toBeInstanceOf(UserDashboard::class);
});

it('dashboard scopeDefault returns only default dashboards', function (): void {
    Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    Dashboard::create([
        'name' => 'Secondary',
        'slug' => 'secondary',
        'is_default' => false,
    ]);

    $defaults = Dashboard::default()->get();
    expect($defaults)->toHaveCount(1);
    expect($defaults->first()->slug)->toBe('main');
});

it('dashboard scopeOrdered returns sorted by sort_order', function (): void {
    Dashboard::create(['name' => 'C', 'slug' => 'c', 'sort_order' => 3]);
    Dashboard::create(['name' => 'A', 'slug' => 'a', 'sort_order' => 1]);
    Dashboard::create(['name' => 'B', 'slug' => 'b', 'sort_order' => 2]);

    $ordered = Dashboard::ordered()->get();
    expect($ordered->pluck('slug')->toArray())->toBe(['a', 'b', 'c']);
});

it('dashboard slug is the route key', function (): void {
    $dashboard = new Dashboard;
    expect($dashboard->getRouteKeyName())->toBe('slug');
});

// --- DashboardWidget model ---

it('dashboard widget belongsTo dashboard', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    $widget = DashboardWidget::create([
        'dashboard_id' => $dashboard->id,
        'widget_class' => 'App\\Widgets\\StatWidget',
        'widget_key' => 'stat-1',
        'label' => 'Stat One',
        'type' => 'stat',
        'is_active' => true,
    ]);

    expect($widget->dashboard)->toBeInstanceOf(Dashboard::class);
    expect($widget->dashboard->id)->toBe($dashboard->id);
});

it('dashboard widget scopeActive filters active widgets', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    DashboardWidget::create([
        'dashboard_id' => $dashboard->id,
        'widget_class' => 'App\\Widgets\\StatWidget',
        'widget_key' => 'stat-1',
        'label' => 'Active Widget',
        'type' => 'stat',
        'is_active' => true,
    ]);

    DashboardWidget::create([
        'dashboard_id' => $dashboard->id,
        'widget_class' => 'App\\Widgets\\ChartWidget',
        'widget_key' => 'chart-1',
        'label' => 'Inactive Widget',
        'type' => 'line',
        'is_active' => false,
    ]);

    $active = DashboardWidget::active()->get();
    expect($active)->toHaveCount(1);
    expect($active->first()->widget_key)->toBe('stat-1');
});

it('dashboard widget casts type to WidgetType enum', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    $widget = DashboardWidget::create([
        'dashboard_id' => $dashboard->id,
        'widget_class' => 'App\\Widgets\\StatWidget',
        'widget_key' => 'stat-1',
        'label' => 'Stat Widget',
        'type' => 'stat',
        'is_active' => true,
    ]);

    $widget->refresh();
    expect($widget->type)->toBeInstanceOf(WidgetType::class);
    expect($widget->type)->toBe(WidgetType::STAT);
});

// --- DashboardPreset model ---

it('dashboard preset belongsTo dashboard', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    $preset = DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'Test Preset',
        'layout' => [],
        'is_system' => false,
    ]);

    expect($preset->dashboard)->toBeInstanceOf(Dashboard::class);
    expect($preset->dashboard->id)->toBe($dashboard->id);
});

it('dashboard preset scopeSystem returns only system presets', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'System Preset',
        'layout' => [],
        'is_system' => true,
    ]);

    DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'User Preset',
        'layout' => [],
        'is_system' => false,
    ]);

    $systemPresets = DashboardPreset::system()->get();
    expect($systemPresets)->toHaveCount(1);
    expect($systemPresets->first()->name)->toBe('System Preset');
});

it('dashboard preset scopeUser returns only user presets', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'System Preset',
        'layout' => [],
        'is_system' => true,
    ]);

    DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'User Preset',
        'layout' => [],
        'is_system' => false,
    ]);

    $userPresets = DashboardPreset::user()->get();
    expect($userPresets)->toHaveCount(1);
    expect($userPresets->first()->name)->toBe('User Preset');
});

// --- UserDashboard model ---

it('user dashboard morphTo user', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $userDashboard = UserDashboard::create([
        'user_type' => get_class($user),
        'user_id' => $user->id,
        'dashboard_id' => $dashboard->id,
        'layout' => [],
    ]);

    expect($userDashboard->user)->toBeInstanceOf(TestUser::class);
    expect($userDashboard->user->id)->toBe($user->id);
});

it('user dashboard belongsTo dashboard', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $userDashboard = UserDashboard::create([
        'user_type' => get_class($user),
        'user_id' => $user->id,
        'dashboard_id' => $dashboard->id,
        'layout' => [],
    ]);

    expect($userDashboard->dashboard)->toBeInstanceOf(Dashboard::class);
    expect($userDashboard->dashboard->id)->toBe($dashboard->id);
});
