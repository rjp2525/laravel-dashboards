<?php

namespace Reno\Dashboard\Console;

use Illuminate\Console\Command;
use Reno\Dashboard\DashboardManager;
use Spatie\Permission\Models\Permission;

class SyncPermissionsCommand extends Command
{
    protected $signature = 'dashboard:permissions';

    protected $description = 'Sync dashboard widget permissions with Spatie Permission tables';

    public function handle(DashboardManager $manager): int
    {
        if (! class_exists(Permission::class)) {
            $this->error('spatie/laravel-permission is not installed.');

            return self::FAILURE;
        }

        /** @var class-string<\Spatie\Permission\Models\Permission> $permissionClass */
        $permissionClass = config('permission.models.permission', Permission::class);

        $widgets = $manager->getWidgets();
        /** @var array<int, string> $permissions */
        $permissions = [];

        foreach ($widgets as $widget) {
            $widgetPerms = $widget->getRequiredPermissions();
            $permissions = array_merge($permissions, $widgetPerms);
        }

        $permissions = array_unique($permissions);

        $created = 0;
        foreach ($permissions as $permission) {
            $permissionClass::findOrCreate($permission); /** @phpstan-ignore staticMethod.notFound */
            $created++;
        }

        $this->info("Synced {$created} permissions.");

        return self::SUCCESS;
    }
}
