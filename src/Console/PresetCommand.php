<?php

namespace Reno\Dashboard\Console;

use Illuminate\Console\Command;
use Reno\Dashboard\Models\Dashboard;
use Reno\Dashboard\Models\DashboardPreset;

class PresetCommand extends Command
{
    protected $signature = 'dashboard:preset
        {action : Action to perform (list|create|delete)}
        {--dashboard= : Dashboard slug}
        {--name= : Preset name}
        {--system : Mark as system preset}';

    protected $description = 'Manage dashboard presets';

    public function handle(): int
    {
        /** @var string $action */
        $action = $this->argument('action');

        if (! in_array($action, ['list', 'create', 'delete'], true)) {
            $this->error('Invalid action. Use: list, create, delete');

            return self::FAILURE;
        }

        return match ($action) {
            'list' => $this->listPresets(),
            'create' => $this->createPreset(),
            'delete' => $this->deletePreset(),
        };
    }

    protected function listPresets(): int
    {
        /** @var string|null $slug */
        $slug = $this->option('dashboard');
        $query = DashboardPreset::with('dashboard');

        if ($slug) {
            $dashboard = Dashboard::where('slug', $slug)->first();
            if (! $dashboard) {
                $this->error("Dashboard '{$slug}' not found.");

                return self::FAILURE;
            }
            $query->where('dashboard_id', $dashboard->id);
        }

        $presets = $query->get();

        if ($presets->isEmpty()) {
            $this->info('No presets found.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Dashboard', 'Name', 'System', 'Created'],
            $presets->map(fn ($p): array => [
                $p->id,
                $p->dashboard->name ?? '-',
                $p->name,
                $p->is_system ? 'Yes' : 'No',
                $p->created_at?->diffForHumans(),
            ]),
        );

        return self::SUCCESS;
    }

    protected function createPreset(): int
    {
        /** @var string|null $slug */
        $slug = $this->option('dashboard') ?? $this->ask('Dashboard slug?');
        /** @var string|null $name */
        $name = $this->option('name') ?? $this->ask('Preset name?');

        $dashboard = Dashboard::where('slug', $slug)->first();

        if (! $dashboard) {
            $this->error("Dashboard '{$slug}' not found.");

            return self::FAILURE;
        }

        DashboardPreset::create([
            'dashboard_id' => $dashboard->id,
            'name' => $name,
            'layout' => [],
            'is_system' => (bool) $this->option('system'),
        ]);

        $this->info("Preset '{$name}' created.");

        return self::SUCCESS;
    }

    protected function deletePreset(): int
    {
        /** @var string|null $name */
        $name = $this->option('name') ?? $this->ask('Preset name to delete?');

        $preset = DashboardPreset::where('name', $name)->first();

        if (! $preset) {
            $this->error("Preset '{$name}' not found.");

            return self::FAILURE;
        }

        $preset->delete();
        $this->info("Preset '{$name}' deleted.");

        return self::SUCCESS;
    }
}
