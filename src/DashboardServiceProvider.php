<?php

namespace Reno\Dashboard;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Reno\Dashboard\Console\CacheDiscoveryCommand;
use Reno\Dashboard\Console\CacheWarmCommand;
use Reno\Dashboard\Console\ClearDiscoveryCacheCommand;
use Reno\Dashboard\Console\InstallCommand;
use Reno\Dashboard\Console\MakeWidgetCommand;
use Reno\Dashboard\Console\PresetCommand;
use Reno\Dashboard\Console\SyncPermissionsCommand;
use Reno\Dashboard\Discovery\DiscoveredWidget;
use Reno\Dashboard\Discovery\WidgetDiscovery;
use Reno\Dashboard\Models\Dashboard;
use Reno\Dashboard\Models\DashboardPreset;
use Reno\Dashboard\Policies\DashboardPolicy;
use Reno\Dashboard\Policies\PresetPolicy;

class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/dashboard.php', 'dashboard');

        $this->app->singleton(DashboardManager::class, function (Application $app): DashboardManager {
            return new DashboardManager($app);
        });

        $this->app->alias(DashboardManager::class, 'dashboard');
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerCommands();
        $this->registerPolicies();
        $this->registerViews();
        $this->registerLivewireComponents();
        $this->discoverWidgets();
    }

    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/dashboard.php' => config_path('dashboard.php'),
            ], 'dashboard-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'dashboard-migrations');
        }
    }

    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // Defer the catch-all web route so app-defined routes under the
        // dashboard prefix are registered first and take priority.
        $this->app->booted(function (): void {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    protected function registerMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Dashboard::class, DashboardPolicy::class);
        Gate::policy(DashboardPreset::class, PresetPolicy::class);
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                MakeWidgetCommand::class,
                CacheWarmCommand::class,
                PresetCommand::class,
                SyncPermissionsCommand::class,
                CacheDiscoveryCommand::class,
                ClearDiscoveryCacheCommand::class,
            ]);
        }
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'dashboard');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/dashboard'),
            ], 'dashboard-views');
        }
    }

    protected function registerLivewireComponents(): void
    {
        if (! class_exists(\Livewire\Livewire::class)) {
            return;
        }

        \Livewire\Livewire::component('dashboard-widget', Livewire\DashboardWidget::class);
        \Livewire\Livewire::component('livewire-dashboard', Livewire\LivewireDashboard::class);
    }

    protected function discoverWidgets(): void
    {
        if (! config('dashboard.discovery.enabled', false)) {
            return;
        }

        $cachePath = $this->app->bootstrapPath('cache/dashboard-widgets.php');

        /** @var DashboardManager $manager */
        $manager = $this->app->make(DashboardManager::class);
        $discovery = new WidgetDiscovery($manager);

        if (is_file($cachePath)) {
            /** @var array<int, array{key: string, label: string, type: string, source: string, class_name: string, method_name?: string|null, aggregate?: string|null, aggregate_column?: string|null, date_column?: string|null, scope?: string|null, dashboard?: string|null, icon?: string|null, description?: string|null, cache_ttl?: int, permissions?: array<int, string>}> $cached */
            $cached = require $cachePath;

            foreach ($cached as $data) {
                $discovery->registerDiscovered(DiscoveredWidget::fromArray($data));
            }

            return;
        }

        /** @var array<int, string> $paths */
        $paths = config('dashboard.discovery.paths', []);
        $discovery->discover($paths);
    }
}
