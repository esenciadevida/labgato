<?php

namespace App\Policies;

use App\Models\ReferenceRange;
use App\Models\User;

class ReferenceRangePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('reference-ranges.view');
    }

    public function view(User $user, ReferenceRange $referenceRange): bool
    {
        return $user->hasPermission('reference-ranges.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('reference-ranges.create');
    }

    public function update(User $user, ReferenceRange $referenceRange): bool
    {
        return $user->hasPermission('reference-ranges.edit');
    }

    public function publish(User $user, ReferenceRange $referenceRange): bool
    {
        return $user->hasPermission('reference-ranges.publish');
    }

    public function delete(User $user, ReferenceRange $referenceRange): bool
    {
        return $user->hasPermission('reference-ranges.delete');
    }
}
