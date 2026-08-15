<?php

namespace App\Policies;

use App\Models\KnowledgeResource;
use App\Models\User;

class KnowledgePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('knowledge.view');
    }

    public function view(User $user, KnowledgeResource $resource): bool
    {
        return $user->hasPermission('knowledge.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('knowledge.create');
    }

    public function update(User $user, KnowledgeResource $resource): bool
    {
        return $user->hasPermission('knowledge.edit');
    }

    public function publish(User $user, KnowledgeResource $resource): bool
    {
        return $user->hasPermission('knowledge.publish');
    }

    public function delete(User $user, KnowledgeResource $resource): bool
    {
        return $user->hasPermission('knowledge.delete');
    }
}
