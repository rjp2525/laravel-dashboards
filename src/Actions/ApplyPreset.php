<?php

namespace Reno\Dashboard\Actions;

use Illuminate\Contracts\Auth\Authenticatable;
use Reno\Dashboard\Models\DashboardPreset;
use Reno\Dashboard\Models\UserDashboard;

class ApplyPreset
{
    public function execute(Authenticatable $user, string $presetId): UserDashboard
    {
        $preset = DashboardPreset::findOrFail($presetId);

        return UserDashboard::updateOrCreate(
            [
                'user_type' => get_class($user),
                'user_id' => $user->getAuthIdentifier(),
                'dashboard_id' => $preset->dashboard_id,
            ],
            [
                'layout' => $preset->layout,
                'active_preset_id' => $preset->id,
            ],
        );
    }
}
