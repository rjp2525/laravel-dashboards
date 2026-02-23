<?php

namespace Reno\Dashboard\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Reno\Dashboard\Models\Dashboard;
use Reno\Dashboard\Models\DashboardPreset;

class CreatePreset
{
    /** @param array<int, array<string, mixed>> $layout */
    public function execute(Authenticatable $user, string $dashboardSlug, string $name, array $layout, bool $isSystem = false): DashboardPreset
    {
        $dashboard = Dashboard::where('slug', $dashboardSlug)->firstOrFail();

        return DashboardPreset::create([
            'dashboard_id' => $dashboard->id,
            'name' => $name,
            'layout' => $layout,
            'is_system' => $isSystem,
            'created_by_type' => get_class($user),
            'created_by_id' => $user->getAuthIdentifier(),
        ]);
    }
}
