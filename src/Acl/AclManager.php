<?php

namespace Reno\Dashboard\Acl;

use Illuminate\Contracts\Auth\Authenticatable;
use Reno\Dashboard\Contracts\Authorizable;
use Reno\Dashboard\Enums\AclDriver;

class AclManager
{
    public function check(Authenticatable $user, Authorizable $subject): bool
    {
        $driver = $this->resolveDriver();

        return $driver->check($user, $subject);
    }

    protected function resolveDriver(): AclDriverInterface
    {
        $driverConfig = config('dashboard.acl.driver');
        $driver = is_string($driverConfig) ? $driverConfig : 'policy';
        $driverType = AclDriver::from($driver);

        return match ($driverType) {
            AclDriver::POLICY => app(PolicyAclDriver::class),
            AclDriver::SPATIE => app(SpatieAclDriver::class),
            AclDriver::CUSTOM => $this->resolveCustomDriver(),
        };
    }

    protected function resolveCustomDriver(): AclDriverInterface
    {
        $customDriver = config('dashboard.acl.custom_driver');
        $class = is_string($customDriver) ? $customDriver : PolicyAclDriver::class;

        /** @var AclDriverInterface */
        return app($class);
    }
}
