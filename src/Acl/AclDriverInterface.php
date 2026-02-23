<?php

namespace Reno\Dashboard\Acl;

use Illuminate\Contracts\Auth\Authenticatable;
use Reno\Dashboard\Contracts\Authorizable;

interface AclDriverInterface
{
    public function check(Authenticatable $user, Authorizable $subject): bool;
}
