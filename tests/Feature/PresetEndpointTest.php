<?php

use Reno\Dashboard\Models\Dashboard;
use Reno\Dashboard\Models\DashboardPreset;
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

it('lists presets for a dashboard', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'Compact View',
        'layout' => [['key' => 'stat', 'position' => ['x' => 0, 'y' => 0, 'w' => 3, 'h' => 1]]],
        'is_system' => false,
    ]);

    DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'Default Layout',
        'layout' => [['key' => 'chart', 'position' => ['x' => 0, 'y' => 0, 'w' => 6, 'h' => 3]]],
        'is_system' => true,
    ]);

    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $response = $this->actingAs($user)
        ->getJson(route('dashboard.presets.index', ['slug' => 'main']))
        ->assertOk();

    $data = $response->json('data');
    expect($data)->toHaveCount(2);
});

it('creates a preset', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $layout = [
        ['key' => 'test-stat', 'position' => ['x' => 0, 'y' => 0, 'w' => 3, 'h' => 1]],
    ];

    $this->actingAs($user)
        ->postJson(route('dashboard.presets.store', ['slug' => 'main']), [
            'name' => 'My Custom Preset',
            'layout' => $layout,
        ])
        ->assertStatus(201)
        ->assertJsonPath('data.name', 'My Custom Preset')
        ->assertJsonPath('data.dashboard_id', $dashboard->id);

    expect(DashboardPreset::count())->toBe(1);

    $preset = DashboardPreset::first();
    expect($preset->name)->toBe('My Custom Preset');
    expect($preset->layout)->toHaveCount(1);
    expect($preset->created_by_type)->toBe(get_class($user));
    expect($preset->created_by_id)->toEqual($user->id);
});

it('shows a single preset', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    $preset = DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'Test Preset',
        'layout' => [['key' => 'stat', 'position' => ['x' => 0, 'y' => 0, 'w' => 3, 'h' => 1]]],
        'is_system' => false,
    ]);

    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $this->actingAs($user)
        ->getJson(route('dashboard.presets.show', ['slug' => 'main', 'presetId' => $preset->id]))
        ->assertOk()
        ->assertJsonPath('data.id', $preset->id)
        ->assertJsonPath('data.name', 'Test Preset');
});

it('updates a preset', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $preset = DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'Old Name',
        'layout' => [['key' => 'stat', 'position' => ['x' => 0, 'y' => 0, 'w' => 3, 'h' => 1]]],
        'is_system' => false,
        'created_by_type' => get_class($user),
        'created_by_id' => $user->id,
    ]);

    $newLayout = [
        ['key' => 'chart', 'position' => ['x' => 0, 'y' => 0, 'w' => 6, 'h' => 3]],
    ];

    $this->actingAs($user)
        ->putJson(route('dashboard.presets.update', ['slug' => 'main', 'presetId' => $preset->id]), [
            'name' => 'Updated Name',
            'layout' => $newLayout,
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Updated Name');

    $preset->refresh();
    expect($preset->name)->toBe('Updated Name');
    expect($preset->layout)->toHaveCount(1);
    expect($preset->layout[0]['key'])->toBe('chart');
});

it('deletes a preset', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $preset = DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'To Delete',
        'layout' => [],
        'is_system' => false,
        'created_by_type' => get_class($user),
        'created_by_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->deleteJson(route('dashboard.presets.destroy', ['slug' => 'main', 'presetId' => $preset->id]))
        ->assertOk()
        ->assertJsonFragment(['message' => 'Preset deleted.']);

    expect(DashboardPreset::count())->toBe(0);
});

it('applies a preset to user dashboard', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    $presetLayout = [
        ['key' => 'test-stat', 'position' => ['x' => 0, 'y' => 0, 'w' => 4, 'h' => 2]],
        ['key' => 'test-chart', 'position' => ['x' => 4, 'y' => 0, 'w' => 8, 'h' => 3]],
    ];

    $preset = DashboardPreset::create([
        'dashboard_id' => $dashboard->id,
        'name' => 'Apply This',
        'layout' => $presetLayout,
        'is_system' => true,
    ]);

    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $this->actingAs($user)
        ->postJson(route('dashboard.presets.apply', ['slug' => 'main', 'presetId' => $preset->id]))
        ->assertOk()
        ->assertJsonFragment(['message' => 'Preset applied.']);

    $userDashboard = UserDashboard::first();
    expect($userDashboard)->not->toBeNull();
    expect($userDashboard->active_preset_id)->toBe($preset->id);
    expect($userDashboard->layout)->toHaveCount(2);
});

it('returns empty collection when no presets', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $response = $this->actingAs($user)
        ->getJson(route('dashboard.presets.index', ['slug' => 'main']))
        ->assertOk();

    $data = $response->json('data');
    expect($data)->toBeArray();
    expect($data)->toBeEmpty();
});
