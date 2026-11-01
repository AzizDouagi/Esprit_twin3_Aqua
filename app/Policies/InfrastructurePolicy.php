<?php

namespace App\Policies;

use App\Models\Infrastructure;
use App\Models\User;

class InfrastructurePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('admin.access');
    }

    public function view(User $user, Infrastructure $infrastructure): bool
    {
        return $user->can('admin.access');
    }

    public function create(User $user): bool
    {
        return $user->can('infrastructures.create');
    }

    public function update(User $user, Infrastructure $infrastructure): bool
    {
        return $user->can('infrastructures.update');
    }

    public function delete(User $user, Infrastructure $infrastructure): bool
    {
        return $user->can('infrastructures.delete');
    }

    public function restore(User $user, Infrastructure $infrastructure): bool
    {
        return $user->can('infrastructures.delete');
    }

    public function forceDelete(User $user, Infrastructure $infrastructure): bool
    {
        return $user->can('infrastructures.delete');
    }
}
