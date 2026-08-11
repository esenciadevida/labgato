<?php

namespace App\Policies;

use App\Models\Calculator;
use App\Models\User;

class CalculatorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('calculators.view');
    }

    public function view(User $user, Calculator $calculator): bool
    {
        return $user->hasPermission('calculators.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('calculators.create');
    }

    public function update(User $user, Calculator $calculator): bool
    {
        return $user->hasPermission('calculators.edit');
    }

    public function publish(User $user, Calculator $calculator): bool
    {
        return $user->hasPermission('calculators.publish');
    }

    public function delete(User $user, Calculator $calculator): bool
    {
        return $user->hasPermission('calculators.delete');
    }
}
