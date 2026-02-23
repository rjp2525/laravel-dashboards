<?php

namespace Reno\Dashboard\Acl;

use Illuminate\Contracts\Auth\Authenticatable;
use Reno\Dashboard\Contracts\Authorizable;
use Reno\Dashboard\Widgets\AbstractWidget;
use RuntimeException;

class SpatieAclDriver implements AclDriverInterface
{
    public function check(Authenticatable $user, Authorizable $subject): bool
    {
        if (! method_exists($user, 'hasPermissionTo')) {
            throw new RuntimeException('Spatie ACL driver requires spatie/laravel-permission to be installed and the HasPermissions trait on the User model.');
        }

        if ($subject instanceof AbstractWidget) {
            $permissions = $subject->getRequiredPermissions();
            if ($permissions === []) {
                return true;
            }
            foreach ($permissions as $permission) {
                if (! $user->hasPermissionTo($permission)) {
                    return false;
                }
            }

            return true;
        }

        return $subject->authorize($user);
    }
}
