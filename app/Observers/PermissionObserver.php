<?php

namespace App\Observers;

use App\Models\Permission;
use App\Services\PermissionCacheService;

class PermissionObserver
{
    public function __construct(
        private PermissionCacheService $permissionCache,
    ) {}

    public function created(Permission $permission): void
    {
        $this->permissionCache->invalidateAllUsers();
    }

    public function updated(Permission $permission): void
    {
        $this->permissionCache->invalidateAllUsers();
    }

    public function deleted(Permission $permission): void
    {
        $this->permissionCache->invalidateAllUsers();
    }
}
