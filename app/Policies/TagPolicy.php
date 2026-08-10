<?php

namespace App\Policies;

use App\Models\User;

class TagPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('taxonomy.view');
    }

    public function view(User $user): bool
    {
        return $user->hasPermission('taxonomy.view');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('taxonomy.manage');
    }
}
