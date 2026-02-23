<?php

namespace Reno\Dashboard\Console;

use Illuminate\Console\Command;
use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\Discovery\WidgetDiscovery;

class CacheDiscoveryCommand extends Command
{
    protected $signature = 'dashboard:discover-cache';

    protected $description = 'Cache discovered dashboard widgets for production';

    public function handle(): int
    {
        /** @var array<int, string> $paths */
        $paths = config('dashboard.discovery.paths', []);

        $discovery = new WidgetDiscovery(app(DashboardManager::class));
        $widgets = $discovery->scanPaths($paths);

        $data = array_map(fn ($w) => $w->toArray(), $widgets);

        $cachePath = $this->laravel->bootstrapPath('cache/dashboard-widgets.php');

        file_put_contents(
            $cachePath,
            '<?php return '.var_export($data, true).';'.PHP_EOL,
        );

        $this->components->info(sprintf('Cached %d discovered widget(s).', count($widgets)));

        return self::SUCCESS;
    }
}
