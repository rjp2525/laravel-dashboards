<?php

namespace Reno\Dashboard\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Reno\Dashboard\DashboardManager;
use Reno\Dashboard\Models\Dashboard;
use Reno\Dashboard\Models\DashboardPreset;
use Reno\Dashboard\Models\UserDashboard;
use Reno\Dashboard\Widgets\AbstractWidget;

class LoadDashboardLayout
{
    public function __construct(
        protected DashboardManager $manager,
    ) {}

    /** @return array<int, array<string, mixed>> */
    public function execute(Authenticatable $user, string $dashboardSlug): array
    {
        $dashboard = Dashboard::where('slug', $dashboardSlug)->firstOrFail();

        // Priority 1: User's saved layout
        $userDashboard = UserDashboard::where('user_type', get_class($user))
            ->where('user_id', $user->getAuthIdentifier())
            ->where('dashboard_id', $dashboard->id)
            ->first();

        if ($userDashboard?->layout) {
            /** @var array<int, array<string, mixed>> */
            return $userDashboard->layout;
        }

        if ($userDashboard?->active_preset_id) {
            $preset = DashboardPreset::find($userDashboard->active_preset_id);
            if ($preset) {
                /** @var array<int, array<string, mixed>> */
                return $preset->layout;
            }
        }

        $systemPreset = $dashboard->presets()->where('is_system', true)->first();
        if ($systemPreset) {
            /** @var array<int, array<string, mixed>> */
            return $systemPreset->layout;
        }
        $widgets = $this->manager->getWidgets($dashboardSlug);

        /** @var array<int, array<string, mixed>> */
        return collect($widgets)->map(fn (AbstractWidget $widget): array => [
            'key' => $widget->key(),
            'position' => $widget->defaultPosition()->toArray(),
        ])->values()->toArray();
    }
}
