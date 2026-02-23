<?php

namespace Reno\Dashboard\Console;

use Illuminate\Console\Command;
use Reno\Dashboard\Models\Dashboard;

class InstallCommand extends Command
{
    protected $signature = 'dashboard:install';

    protected $description = 'Install the dashboard package';

    public function handle(): int
    {
        $this->info('Installing Laravel Dashboard...');

        $this->call('vendor:publish', [
            '--tag' => 'dashboard-config',
        ]);

        $this->info('Configuration published.');

        if ($this->confirm('Run database migrations?', true)) {
            $this->call('migrate');
            $this->info('Migrations completed.');
        }

        if ($this->confirm('Create a sample dashboard?', true)) {
            $this->createSampleDashboard();
        }

        $this->info('Laravel Dashboard installed successfully!');
        $this->newLine();
        $this->line('Next steps:');
        $this->line('  1. Create widgets: php artisan dashboard:widget MyWidget');
        $this->line('  2. Register widgets in a service provider');
        $this->line('  3. Visit /dashboard to see your dashboard');

        return self::SUCCESS;
    }

    protected function createSampleDashboard(): void
    {
        $dashboard = Dashboard::create([
            'name' => 'Main Dashboard',
            'slug' => 'main',
            'description' => 'Your default dashboard',
            'is_default' => true,
            'sort_order' => 0,
        ]);

        $this->info("Created dashboard: {$dashboard->name} (/{$dashboard->slug})");
    }
}
