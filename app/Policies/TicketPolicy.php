<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $u): bool {
        return $u->hasAnyRole(['Administrador','Encargado de departamento','Rector','Usuario']);
    }

    public function view(User $u, Ticket $t): bool {
        if ($u->hasRole('Administrador') || $u->hasRole('Rector')) return true;
        if ($u->hasRole('Encargado de departamento')) return $t->departamento_id === $u->department_id;
        return $t->usuario_id === $u->id || $t->asignado_id === $u->id;
    }

    public function create(User $u): bool {
        return $u->hasAnyRole(['Administrador','Encargado de departamento','Usuario','Rector']);
    }

    public function update(User $u, Ticket $t): bool {
        if ($u->hasRole('Administrador')) return true;
        if ($u->hasRole('Encargado de departamento')) return $t->departamento_id === $u->department_id;
        // autor puede editar mientras esté Abierto
        return $t->usuario_id === $u->id && $t->estado === 'Abierto';
    }

    public function delete(User $u, Ticket $t): bool {
        return $u->hasRole('Administrador');
    }

    public function assign(User $u, Ticket $t): bool {
        if ($u->hasRole('Administrador')) return true;
        return $u->hasRole('Encargado de departamento') && $t->departamento_id === $u->department_id;
    }

    public function comment(User $u, Ticket $t): bool {
        return $this->view($u, $t); // cualquiera con acceso puede comentar
    }

    public function transition(User $u, Ticket $t, string $to): bool {
        // mapa de transiciones válidas
        $valid = [
            'Abierto'     => ['En proceso','Cerrado'],
            'En proceso'  => ['Resuelto','Cerrado'],
            'Resuelto'    => ['Cerrado','En proceso'],
            'Cerrado'     => [],
        ];
        if (!in_array($to, $valid[$t->estado] ?? [], true)) return false;

        if ($u->hasRole('Administrador')) return true;
        if ($u->hasRole('Encargado de departamento')) return $t->departamento_id === $u->department_id;
        // autor puede cerrar su propio ticket sólo si está Resuelto
        if ($u->id === $t->usuario_id) return $t->estado === 'Resuelto' && $to === 'Cerrado';

        // asignado puede mover entre Abierto/En proceso/Resuelto
        if ($u->id === $t->asignado_id) return in_array($to, ['En proceso','Resuelto'], true);

        return false;
    }
}
