<?php

namespace Reno\Dashboard\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Reno\Dashboard\DashboardServiceProvider;
use Reno\Dashboard\Facades\Dashboard;
use Reno\Dashboard\Tests\Fixtures\TestUser;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        $providers = [
            DashboardServiceProvider::class,
        ];

        if (class_exists(\Livewire\LivewireServiceProvider::class)) {
            $providers[] = \Livewire\LivewireServiceProvider::class;
        }

        return $providers;
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Dashboard' => Dashboard::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('dashboard.routing.middleware', ['web']);
        $app['config']->set('dashboard.routing.api_middleware', []);
        $app['config']->set('dashboard.database.user_model', TestUser::class);
    }
}
