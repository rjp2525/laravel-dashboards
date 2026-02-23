<?php

use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\Tests\Fixtures\TestChartWidget;
use Reno\Dashboard\Tests\Fixtures\TestStatWidget;
use Reno\Dashboard\Tests\Fixtures\TestUser;

beforeEach(function (): void {
    $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table): void {
        $table->id();
        $table->string('name')->default('');
        $table->string('email')->default('');
        $table->timestamps();
    });

    $manager = app(DashboardManager::class);
    $manager->register(TestStatWidget::class);
    $manager->register(TestChartWidget::class);
});

it('returns widget data for authenticated user', function (): void {
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $this->actingAs($user)
        ->getJson(route('dashboard.widget.data', ['key' => 'test-stat']))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'value',
                'previous_value',
                'change',
                'change_percent',
                'change_direction',
                'series',
                'labels',
                'rows',
                'columns',
                'meta',
                'updated_at',
            ],
        ])
        ->assertJsonPath('data.value', 100)
        ->assertJsonPath('data.previous_value', 80);
});

it('returns chart widget data with series', function (): void {
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $response = $this->actingAs($user)
        ->getJson(route('dashboard.widget.data', ['key' => 'test-chart']))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'series',
                'labels',
            ],
        ]);

    $data = $response->json('data');

    expect($data['series'])->toHaveCount(1);
    expect($data['series'][0]['name'])->toBe('Revenue');
    expect($data['series'][0]['data'])->toBe([100, 200, 300, 400]);
    expect($data['labels'])->toBe(['Jan', 'Feb', 'Mar', 'Apr']);
});

it('returns 500 for unknown widget', function (): void {
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $this->actingAs($user)
        ->getJson(route('dashboard.widget.data', ['key' => 'nonexistent-widget']))
        ->assertStatus(500);
});

it('supports period parameter', function (): void {
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $this->actingAs($user)
        ->getJson(route('dashboard.widget.data', ['key' => 'test-stat', 'period' => '7d']))
        ->assertOk()
        ->assertJsonPath('data.value', 100);
});

it('supports timezone parameter', function (): void {
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $this->actingAs($user)
        ->getJson(route('dashboard.widget.data', ['key' => 'test-stat', 'timezone' => 'America/New_York']))
        ->assertOk()
        ->assertJsonPath('data.value', 100);
});

it('supports ETag and returns 304 on matching If-None-Match', function (): void {
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $firstResponse = $this->actingAs($user)
        ->getJson(route('dashboard.widget.data', ['key' => 'test-stat']));

    $firstResponse->assertOk();
    $etag = $firstResponse->headers->get('ETag');

    expect($etag)->not->toBeNull();

    $this->actingAs($user)
        ->getJson(route('dashboard.widget.data', ['key' => 'test-stat']), [
            'If-None-Match' => $etag,
        ])
        ->assertStatus(304);
});
