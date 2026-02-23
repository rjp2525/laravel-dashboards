<?php

namespace Reno\Dashboard\Acl;

use Illuminate\Contracts\Auth\Authenticatable;
use Reno\Dashboard\Contracts\Authorizable;

class PolicyAclDriver implements AclDriverInterface
{
    public function check(Authenticatable $user, Authorizable $subject): bool
    {
        return $subject->authorize($user);
    }
}
