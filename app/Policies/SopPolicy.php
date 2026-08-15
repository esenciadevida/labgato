<?php

namespace App\Policies;

use App\Models\Sop;
use App\Models\User;

class SopPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('sops.view');
    }

    public function view(User $user, Sop $sop): bool
    {
        return $user->hasPermission('sops.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('sops.create');
    }

    public function update(User $user, Sop $sop): bool
    {
        return $user->hasPermission('sops.edit');
    }

    public function publish(User $user, Sop $sop): bool
    {
        return $user->hasPermission('sops.publish');
    }

    public function delete(User $user, Sop $sop): bool
    {
        return $user->hasPermission('sops.delete');
    }
}
