<?php

use Illuminate\Contracts\Auth\Authenticatable;
use Reno\Dashboard\Models\Dashboard;
use Reno\Dashboard\Models\DashboardPreset;
use Reno\Dashboard\Policies\DashboardPolicy;
use Reno\Dashboard\Policies\PresetPolicy;
use Reno\Dashboard\Tests\Fixtures\TestUser;

test('DashboardPolicy view() returns true', function (): void {
    $policy = new DashboardPolicy;
    $user = mock(Authenticatable::class);
    $dashboard = new Dashboard;

    expect($policy->view($user, $dashboard))->toBeTrue();
});

test('DashboardPolicy editLayout() returns true', function (): void {
    $policy = new DashboardPolicy;
    $user = mock(Authenticatable::class);
    $dashboard = new Dashboard;

    expect($policy->editLayout($user, $dashboard))->toBeTrue();
});

test('DashboardPolicy manage() returns true', function (): void {
    $policy = new DashboardPolicy;
    $user = mock(Authenticatable::class);
    $dashboard = new Dashboard;

    expect($policy->manage($user, $dashboard))->toBeTrue();
});

beforeEach(function (): void {
    $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table): void {
        $table->id();
        $table->string('name')->default('');
        $table->string('email')->default('');
        $table->timestamps();
    });
});

test('PresetPolicy view() returns true', function (): void {
    $policy = new PresetPolicy;
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);
    $preset = new DashboardPreset;

    expect($policy->view($user, $preset))->toBeTrue();
});

test('PresetPolicy create() returns true when config allows (default)', function (): void {
    config()->set('dashboard.presets.allow_user_presets', true);

    $policy = new PresetPolicy;
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    expect($policy->create($user))->toBeTrue();
});

test('PresetPolicy create() returns false when config disallows', function (): void {
    config()->set('dashboard.presets.allow_user_presets', false);

    $policy = new PresetPolicy;
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    expect($policy->create($user))->toBeFalse();
});

test('PresetPolicy update() returns false for system presets', function (): void {
    $policy = new PresetPolicy;
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $dashboard = Dashboard::create(['name' => 'Test', 'slug' => 'policy-update-system']);

    $preset = DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'System Preset',
        'layout' => [],
        'is_system' => true,
        'created_by_type' => TestUser::class,
        'created_by_id' => $user->id,
    ]);

    expect($policy->update($user, $preset))->toBeFalse();
});

test('PresetPolicy update() returns true when user is creator', function (): void {
    $policy = new PresetPolicy;
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $dashboard = Dashboard::create(['name' => 'Test', 'slug' => 'policy-update-creator']);

    $preset = DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'User Preset',
        'layout' => [],
        'is_system' => false,
        'created_by_type' => TestUser::class,
        'created_by_id' => $user->id,
    ]);

    expect($policy->update($user, $preset))->toBeTrue();
});

test('PresetPolicy delete() returns false for system presets', function (): void {
    $policy = new PresetPolicy;
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $dashboard = Dashboard::create(['name' => 'Test', 'slug' => 'policy-delete-system']);

    $preset = DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'System Preset',
        'layout' => [],
        'is_system' => true,
        'created_by_type' => TestUser::class,
        'created_by_id' => $user->id,
    ]);

    expect($policy->delete($user, $preset))->toBeFalse();
});

test('PresetPolicy delete() returns true when user is creator', function (): void {
    $policy = new PresetPolicy;
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $dashboard = Dashboard::create(['name' => 'Test', 'slug' => 'policy-delete-creator']);

    $preset = DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'User Preset',
        'layout' => [],
        'is_system' => false,
        'created_by_type' => TestUser::class,
        'created_by_id' => $user->id,
    ]);

    expect($policy->delete($user, $preset))->toBeTrue();
});
