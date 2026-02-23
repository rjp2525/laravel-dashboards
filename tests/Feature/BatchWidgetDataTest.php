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

it('returns data for multiple widgets in batch', function (): void {
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $response = $this->actingAs($user)
        ->postJson(route('dashboard.widget.batch'), [
            'widgets' => ['test-stat', 'test-chart'],
        ])
        ->assertOk();

    $data = $response->json('data');

    expect($data)->toHaveKeys(['test-stat', 'test-chart']);
    expect($data['test-stat']['status'])->toBe('ok');
    expect($data['test-stat']['data']['value'])->toBe(100);
    expect($data['test-chart']['status'])->toBe('ok');
    expect($data['test-chart']['data']['series'])->toHaveCount(1);
});

it('handles mix of valid and invalid widget keys', function (): void {
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $response = $this->actingAs($user)
        ->postJson(route('dashboard.widget.batch'), [
            'widgets' => ['test-stat', 'nonexistent-widget'],
        ])
        ->assertOk();

    $data = $response->json('data');

    expect($data['test-stat']['status'])->toBe('ok');
    expect($data['test-stat']['data']['value'])->toBe(100);

    expect($data['nonexistent-widget']['status'])->toBe('error');
    expect($data['nonexistent-widget']['error'])->toContain('nonexistent-widget');
});

it('supports period and timezone params', function (): void {
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $response = $this->actingAs($user)
        ->postJson(route('dashboard.widget.batch'), [
            'widgets' => ['test-stat'],
            'period' => '7d',
            'timezone' => 'Europe/London',
        ])
        ->assertOk();

    $data = $response->json('data');
    expect($data['test-stat']['status'])->toBe('ok');
    expect($data['test-stat']['data']['value'])->toBe(100);
});
