<?php

namespace Reno\Dashboard\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;
use Reno\Dashboard\Models\DashboardPreset;

class PresetPolicy
{
    use HandlesAuthorization;

    public function view(?Authenticatable $user, DashboardPreset $preset): bool
    {
        return true;
    }

    public function create(Authenticatable $user): bool
    {
        return (bool) config('dashboard.presets.allow_user_presets', true);
    }

    public function update(Authenticatable $user, DashboardPreset $preset): bool
    {
        return $this->canModify($user, $preset);
    }

    public function delete(Authenticatable $user, DashboardPreset $preset): bool
    {
        return $this->canModify($user, $preset);
    }

    private function canModify(Authenticatable $user, DashboardPreset $preset): bool
    {
        if ($preset->is_system) {
            return false;
        }

        /** @var int|string|null $userId */
        $userId = $user->getAuthIdentifier();

        if ($preset->created_by_id === $userId) {
            return true;
        }

        return $preset->created_by_type === get_class($user);
    }
}
