<?php

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;

class IncidentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('incidents.manage');
    }

    public function view(User $user, Incident $incident): bool
    {
        return $user->can('incidents.manage') || $incident->declarant_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('incidents.manage') || $user->can('incidents.report');
    }

    public function update(User $user, Incident $incident): bool
    {
        return $user->can('incidents.manage');
    }

    public function delete(User $user, Incident $incident): bool
    {
        return $user->hasRole(User::ROLE_ADMIN);
    }

    public function comment(User $user, Incident $incident): bool
    {
        return $this->view($user, $incident);
    }
}
