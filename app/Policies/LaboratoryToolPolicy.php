<?php

namespace App\Policies;

use App\Models\LaboratoryTool;
use App\Models\User;

class LaboratoryToolPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('tools.view');
    }

    public function view(User $user, LaboratoryTool $tool): bool
    {
        return $user->hasPermission('tools.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('tools.create');
    }

    public function update(User $user, LaboratoryTool $tool): bool
    {
        return $user->hasPermission('tools.edit');
    }

    public function publish(User $user, LaboratoryTool $tool): bool
    {
        return $user->hasPermission('tools.publish');
    }

    public function delete(User $user, LaboratoryTool $tool): bool
    {
        return $user->hasPermission('tools.delete');
    }
}
