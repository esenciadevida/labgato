<?php

namespace App\Policies;

use App\Models\User;

class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('audit-logs.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('audit-logs.view');
    }
}
