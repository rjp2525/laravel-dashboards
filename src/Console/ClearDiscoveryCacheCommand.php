<?php

namespace Reno\Dashboard\Console;

use Illuminate\Console\Command;

class ClearDiscoveryCacheCommand extends Command
{
    protected $signature = 'dashboard:discover-clear';

    protected $description = 'Clear the cached dashboard widget manifest';

    public function handle(): int
    {
        $cachePath = $this->laravel->bootstrapPath('cache/dashboard-widgets.php');

        if (is_file($cachePath)) {
            unlink($cachePath);
            $this->components->info('Dashboard widget cache cleared.');
        } else {
            $this->components->info('No dashboard widget cache to clear.');
        }

        return self::SUCCESS;
    }
}
