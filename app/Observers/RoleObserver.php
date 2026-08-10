<?php

namespace App\Observers;

use App\Models\Role;
use App\Services\PermissionCacheService;

class RoleObserver
{
    public function __construct(
        private PermissionCacheService $permissionCache,
    ) {}

    public function updated(Role $role): void
    {
        $this->permissionCache->invalidateAllUsersWithRole($role->id);
    }

    public function deleted(Role $role): void
    {
        $this->permissionCache->invalidateAllUsersWithRole($role->id);
    }
}
