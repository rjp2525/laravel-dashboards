<?php

use Reno\Dashboard\Tests\Fixtures\TestUser;

beforeEach(function (): void {
    $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table): void {
        $table->id();
        $table->string('name')->default('');
        $table->string('email')->default('');
        $table->timestamps();
    });
});

it('returns 404 for non-existent dashboard slug', function (): void {
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $this->actingAs($user)
        ->get('/dashboard/nonexistent')
        ->assertNotFound();
});

it('returns 404 when no default dashboard exists and no slug provided', function (): void {
    $user = TestUser::create(['name' => 'Test', 'email' => 'test@example.com']);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertNotFound();
});
