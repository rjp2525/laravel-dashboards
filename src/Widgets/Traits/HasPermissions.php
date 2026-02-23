<?php

namespace Reno\Dashboard\Widgets\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Reno\Dashboard\Acl\AclManager;

trait HasPermissions
{
    /** @var array<int, string> */
    protected array $requiredPermissions = [];

    public function authorize(?Authenticatable $user): bool
    {
        if (empty($this->requiredPermissions)) {
            return true;
        }

        if (! $user instanceof Authenticatable) {
            return false;
        }

        /** @var AclManager $aclManager */
        $aclManager = app(AclManager::class);

        return $aclManager->check($user, $this);
    }

    /** @param array<int, string> $permissions */
    public function permissions(array $permissions): static
    {
        $this->requiredPermissions = $permissions;

        return $this;
    }

    /** @return array<int, string> */
    public function getRequiredPermissions(): array
    {
        return $this->requiredPermissions;
    }
}
