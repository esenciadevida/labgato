<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-users');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermission('view-users');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-users');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermission('edit-users');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermission('delete-users');
    }
}
