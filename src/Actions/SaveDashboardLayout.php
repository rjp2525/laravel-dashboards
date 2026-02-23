<?php

namespace Reno\Dashboard\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Reno\Dashboard\Models\Dashboard;
use Reno\Dashboard\Models\UserDashboard;

class SaveDashboardLayout
{
    /**
     * @param  array<int, array<string, mixed>>  $layout
     * @param  array<string, mixed>|null  $filters
     */
    public function execute(Authenticatable $user, string $dashboardSlug, array $layout, ?array $filters = null): UserDashboard
    {
        $dashboard = Dashboard::where('slug', $dashboardSlug)->firstOrFail();

        return UserDashboard::updateOrCreate(
            [
                'user_type' => get_class($user),
                'user_id' => $user->getAuthIdentifier(),
                'dashboard_id' => $dashboard->id,
            ],
            array_filter([
                'layout' => $layout,
                'filters' => $filters,
            ], fn (?array $v): bool => $v !== null),
        );
    }
}
