<?php

namespace App\Policies;

use App\Models\InterpretationResource;
use App\Models\User;

class InterpretationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('interpretation.view');
    }

    public function view(User $user, InterpretationResource $resource): bool
    {
        return $user->hasPermission('interpretation.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('interpretation.create');
    }

    public function update(User $user, InterpretationResource $resource): bool
    {
        return $user->hasPermission('interpretation.edit');
    }

    public function publish(User $user, InterpretationResource $resource): bool
    {
        return $user->hasPermission('interpretation.publish');
    }

    public function delete(User $user, InterpretationResource $resource): bool
    {
        return $user->hasPermission('interpretation.delete');
    }
}
