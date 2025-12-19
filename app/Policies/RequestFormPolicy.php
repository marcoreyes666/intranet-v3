<?php

namespace App\Policies;

use App\Models\RequestForm;
use App\Models\User;

class RequestFormPolicy
{
    /**
     * Admin ve todo por defecto (puedes quitar este before si no lo quieres tan amplio).
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Administrador')) {
            return true;
        }

        return null;
    }

    /**
     * ¿Puede ver el listado general?
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'Administrador',
            'Rector',
            'Encargado de departamento',
            'Usuario',
            'Compras',
            'Contabilidad',
        ]);
    }

    /**
     * ¿Puede ver una solicitud específica?
     */
    public function view(User $user, RequestForm $r): bool
    {
        // Rector ve todo
        if ($user->hasRole('Rector')) {
            return true;
        }

        // Compras / Contabilidad: todo excepto permisos personales
        if ($user->hasAnyRole(['Compras', 'Contabilidad'])) {
            return $r->type !== RequestForm::TYPE_PERMISO;
        }

        // Encargado de depto: solo las de su departamento
        if ($user->hasRole('Encargado de departamento')) {
            return $r->department_id
                && $user->department_id
                && $user->department_id === $r->department_id;
        }

        // Usuario normal: solo las que él creó
        return $r->user_id === $user->id;
    }

    /**
     * Crear nuevas solicitudes (permiso / cheque / compra).
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'Administrador',
            'Rector',
            'Encargado de departamento',
            'Usuario',
        ]);
    }

    /**
     * Aprobar/rechazar un nivel del flujo.
     */
    public function approve(User $user, RequestForm $r): bool
    {
        // Rector puede aprobar cualquier tipo
        if ($user->hasRole('Rector')) {
            return true;
        }

        // Encargado de depto: solo permisos de su departamento
        if ($user->hasRole('Encargado de departamento')) {
            return $r->type === RequestForm::TYPE_PERMISO
                && $user->department_id
                && $user->department_id === $r->department_id;
        }

        // Compras: solo solicitudes de compra
        if ($user->hasRole('Compras')) {
            return $r->type === RequestForm::TYPE_COMPRA;
        }

        // Contabilidad: solo solicitudes de cheque
        if ($user->hasRole('Contabilidad')) {
            return $r->type === RequestForm::TYPE_CHEQUE;
        }

        return false;
    }

    /**
     * Marcar una compra como completada (entregada).
     */
    public function complete(User $user, RequestForm $r): bool
    {
        return $user->hasRole('Compras')
            && $r->type === RequestForm::TYPE_COMPRA;
    }
}
