<?php

namespace Reno\Dashboard\Console;

use Illuminate\Console\Command;
use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\Enums\Period;
use Reno\Dashboard\Models\Dashboard;
use Reno\Dashboard\Support\WidgetContext;

class CacheWarmCommand extends Command
{
    protected $signature = 'dashboard:warm {--dashboard= : Dashboard slug to warm} {--period=30d : Period to warm}';

    protected $description = 'Warm the cache for all dashboard widgets';

    public function handle(DashboardManager $manager): int
    {
        /** @var string|null $slug */
        $slug = $this->option('dashboard');
        /** @var string $periodValue */
        $periodValue = $this->option('period');
        $period = Period::tryFrom($periodValue) ?? Period::THIRTY_DAYS;

        if ($slug) {
            $dashboards = Dashboard::where('slug', $slug)->get();
        } else {
            $dashboards = Dashboard::all();
        }

        if ($dashboards->isEmpty()) {
            $this->warn('No dashboards found.');

            return self::SUCCESS;
        }

        $context = new WidgetContext(period: $period);
        $total = 0;

        foreach ($dashboards as $dashboard) {
            $widgets = $manager->getWidgets($dashboard->slug);

            $this->info("Warming {$dashboard->name} ({$dashboard->slug})...");

            foreach ($widgets as $widget) {
                $widget->forgetCache($widget->cacheKey());
                $widget->resolve($context);
                $total++;
                $this->line("  - {$widget->label()} ({$widget->key()})");
            }
        }

        $this->info("Warmed {$total} widget(s).");

        return self::SUCCESS;
    }
}
