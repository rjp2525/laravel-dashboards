<?php

use Reno\Dashboard\DashboardManager;
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
});

it('exports widget data as CSV', function (): void {
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $response = $this->actingAs($user)
        ->get(route('dashboard.widget.export', ['key' => 'test-stat']))
        ->assertOk();

    expect($response->headers->get('Content-Type'))->toContain('text/csv');
    expect($response->headers->get('Content-Disposition'))->toContain('test-stat-export-');
    expect($response->headers->get('Content-Disposition'))->toContain('.csv');

    $content = $response->streamedContent();
    expect($content)->toContain('Metric');
    expect($content)->toContain('Value');
    expect($content)->toContain('Test Stat');
});

it('returns 500 for unknown widget', function (): void {
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $this->actingAs($user)
        ->get(route('dashboard.widget.export', ['key' => 'nonexistent-widget']))
        ->assertStatus(500);
});
