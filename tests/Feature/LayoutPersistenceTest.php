<?php

use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\Models\Dashboard;
use Reno\Dashboard\Models\UserDashboard;
use Reno\Dashboard\Tests\Fixtures\TestStatWidget;
use Reno\Dashboard\Tests\Fixtures\TestUser;

beforeEach(function (): void {
    $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table): void {
        $table->id();
        $table->string('name')->default('');
        $table->string('email')->default('');
        $table->timestamps();
    });
});

it('saves layout for a dashboard', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $layout = [
        ['key' => 'test-stat', 'position' => ['x' => 0, 'y' => 0, 'w' => 3, 'h' => 1]],
        ['key' => 'test-chart', 'position' => ['x' => 3, 'y' => 0, 'w' => 6, 'h' => 3]],
    ];

    $this->actingAs($user)
        ->putJson(route('dashboard.layout.update', ['slug' => 'main']), [
            'layout' => $layout,
        ])
        ->assertOk()
        ->assertJsonFragment(['message' => 'Layout saved successfully.']);

    $userDashboard = UserDashboard::first();
    expect($userDashboard)->not->toBeNull();
    expect($userDashboard->layout)->toHaveCount(2);
});

it('loads saved layout', function (): void {
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
        'layout' => [
            ['key' => 'widget-1', 'position' => ['x' => 0, 'y' => 0, 'w' => 4, 'h' => 2]],
        ],
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('dashboard.layout.show', ['slug' => 'main']))
        ->assertOk();

    $data = $response->json('data');
    expect($data)->toHaveCount(1);
    expect($data[0]['key'])->toBe('widget-1');
});

it('loads default widget positions when no saved layout', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    $manager = app(DashboardManager::class);
    $manager->register(TestStatWidget::class);

    // Register widget in DB so getWidgets returns it
    $dashboard->widgets()->create([
        'widget_class' => TestStatWidget::class,
        'widget_key' => 'test-stat',
        'label' => 'Test Stat',
        'type' => 'stat',
        'is_active' => true,
    ]);

    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $response = $this->actingAs($user)
        ->getJson(route('dashboard.layout.show', ['slug' => 'main']))
        ->assertOk();

    $data = $response->json('data');
    expect($data)->toBeArray();
    expect($data)->not->toBeEmpty();
    expect($data[0]['key'])->toBe('test-stat');
    expect($data[0]['position'])->toHaveKeys(['x', 'y', 'w', 'h']);
});

it('saves layout with filters', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $layout = [
        ['key' => 'test-stat', 'position' => ['x' => 0, 'y' => 0, 'w' => 3, 'h' => 1]],
    ];

    $filters = ['status' => 'active', 'region' => 'us'];

    $this->actingAs($user)
        ->putJson(route('dashboard.layout.update', ['slug' => 'main']), [
            'layout' => $layout,
            'filters' => $filters,
        ])
        ->assertOk();

    $userDashboard = UserDashboard::first();
    expect($userDashboard->filters)->toBe($filters);
});

it('updates existing layout on re-save', function (): void {
    $dashboard = Dashboard::create([
        'name' => 'Main',
        'slug' => 'main',
        'is_default' => true,
    ]);

    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $initialLayout = [
        ['key' => 'test-stat', 'position' => ['x' => 0, 'y' => 0, 'w' => 3, 'h' => 1]],
    ];

    $this->actingAs($user)
        ->putJson(route('dashboard.layout.update', ['slug' => 'main']), [
            'layout' => $initialLayout,
        ])
        ->assertOk();

    expect(UserDashboard::count())->toBe(1);

    $updatedLayout = [
        ['key' => 'test-stat', 'position' => ['x' => 0, 'y' => 0, 'w' => 6, 'h' => 2]],
        ['key' => 'test-chart', 'position' => ['x' => 6, 'y' => 0, 'w' => 6, 'h' => 3]],
    ];

    $this->actingAs($user)
        ->putJson(route('dashboard.layout.update', ['slug' => 'main']), [
            'layout' => $updatedLayout,
        ])
        ->assertOk();

    // Should still be one record (updateOrCreate)
    expect(UserDashboard::count())->toBe(1);

    $userDashboard = UserDashboard::first();
    expect($userDashboard->layout)->toHaveCount(2);
    expect($userDashboard->layout[0]['position']['w'])->toBe(6);
});
