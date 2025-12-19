<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $u): bool
    {
        return $u->hasAnyRole([
            'Administrador',
            'Sistemas',
            'Encargado de departamento',
            'Rector',
            'Usuario',
        ]);
    }

    public function view(User $u, Ticket $t): bool
    {
        if ($u->hasAnyRole(['Administrador', 'Sistemas', 'Rector'])) {
            return true;
        }

        if ($u->hasRole('Encargado de departamento')) {
            return $t->departamento_id === $u->department_id;
        }

        return $t->usuario_id === $u->id || $t->asignado_id === $u->id;
    }

    public function create(User $u): bool
    {
        return $u->hasAnyRole([
            'Administrador',
            'Sistemas',
            'Encargado de departamento',
            'Usuario',
            'Rector',
        ]);
    }

    public function update(User $u, Ticket $t): bool
    {
        if ($u->hasAnyRole(['Administrador', 'Sistemas'])) {
            return true;
        }

        if ($u->hasRole('Encargado de departamento')) {
            return $t->departamento_id === $u->department_id;
        }

        // Autor puede editar mientras esté Abierto
        return $t->usuario_id === $u->id && $t->estado === 'Abierto';
    }

    public function delete(User $u, Ticket $t): bool
    {
        return $u->hasAnyRole(['Administrador', 'Sistemas']);
    }

    public function assign(User $u, Ticket $t): bool
    {
        if ($u->hasAnyRole(['Administrador', 'Sistemas'])) {
            return true;
        }

        return $u->hasRole('Encargado de departamento')
            && $t->departamento_id === $u->department_id;
    }

    public function comment(User $u, Ticket $t): bool
    {
        // cualquiera que pueda ver el ticket puede comentar
        return $this->view($u, $t);
    }

    public function transition(User $u, Ticket $t, string $to): bool
    {
        // Mapa de transiciones válidas
        $valid = [
            'Abierto'    => ['En proceso', 'Cerrado'],
            'En proceso' => ['Resuelto', 'Cerrado'],
            'Resuelto'   => ['Cerrado', 'En proceso'],
            'Cerrado'    => [],
        ];

        if (!in_array($to, $valid[$t->estado] ?? [], true)) {
            return false;
        }

        // Admin / Sistemas pueden mover libremente dentro de las reglas anteriores
        if ($u->hasAnyRole(['Administrador', 'Sistemas'])) {
            return true;
        }

        // Encargado del departamento
        if ($u->hasRole('Encargado de departamento') && $t->departamento_id === $u->department_id) {
            return true;
        }

        // Autor puede cerrar su propio ticket sólo si está Resuelto → Cerrado
        if ($u->id === $t->usuario_id) {
            return $t->estado === 'Resuelto' && $to === 'Cerrado';
        }

        // Asignado puede mover entre Abierto / En proceso / Resuelto (pero no cerrar)
        if ($u->id === $t->asignado_id) {
            return in_array($to, ['En proceso', 'Resuelto'], true);
        }

        return false;
    }
}
