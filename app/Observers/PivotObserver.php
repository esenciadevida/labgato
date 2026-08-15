<?php

namespace App\Observers;

use App\Services\PermissionCacheService;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PivotObserver
{
    public function __construct(
        private PermissionCacheService $permissionCache,
    ) {}

    public function created(Pivot $pivot): void
    {
        $this->invalidate($pivot);
    }

    public function updated(Pivot $pivot): void
    {
        $this->invalidate($pivot);
    }

    public function deleted(Pivot $pivot): void
    {
        $this->invalidate($pivot);
    }

    private function invalidate(Pivot $pivot): void
    {
        if (in_array($pivot->getTable(), ['permission_role', 'role_user'], true)) {
            $this->permissionCache->invalidateAllUsers();
        }
    }
}
