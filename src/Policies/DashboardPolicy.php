<?php

namespace Reno\Dashboard\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;
use Reno\Dashboard\Models\Dashboard;

class DashboardPolicy
{
    use HandlesAuthorization;

    public function view(?Authenticatable $user, Dashboard $dashboard): bool
    {
        return true;
    }

    public function editLayout(Authenticatable $user, Dashboard $dashboard): bool
    {
        return true;
    }

    public function manage(Authenticatable $user, Dashboard $dashboard): bool
    {
        return true;
    }
}
