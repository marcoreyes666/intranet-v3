<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Event;

class EventPolicy
{
    public function view(User $user, Event $event): bool
    {
        return true; // todos pueden ver
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['Administrador','Encargado de departamento','Rector']);
    }

    public function update(User $user, Event $event): bool
    {
        return $user->hasRole(['Administrador','Encargado de departamento','Rector']);
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->hasRole(['Administrador','Encargado de departamento','Rector']);
    }
}
