<?php

namespace App\Policies;

use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-roles');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-roles');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('manage-roles');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('manage-roles');
    }
}
