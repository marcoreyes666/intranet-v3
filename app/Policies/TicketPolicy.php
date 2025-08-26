<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool {
        return $user->hasAnyRole(['Administrador','Encargado de departamento','Rector']) || true; // todos pueden ver los suyos
    }

    public function view(User $user, Ticket $ticket): bool {
        if ($user->hasAnyRole(['Administrador','Encargado de departamento','Rector'])) return true;
        return $ticket->usuario_id === $user->id || $ticket->asignado_id === $user->id;
    }

    public function create(User $user): bool {
        return $user->exists; // cualquier autenticado
    }

    public function update(User $user, Ticket $ticket): bool {
        if ($user->hasRole('Administrador')) return true;
        if ($user->hasRole('Encargado de departamento')) return true;
        return $ticket->asignado_id === $user->id;
    }

    public function delete(User $user, Ticket $ticket): bool {
        return $user->hasRole('Administrador');
    }
}
