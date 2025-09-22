<?php

// app/Policies/RequestFormPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\RequestForm;

class RequestFormPolicy
{
    public function viewAny(User $user): bool { return $user->hasAnyRole(['Administrador','Rector','Encargado de departamento','Usuario','Compras','Contabilidad']); }
    public function view(User $user, RequestForm $r): bool {
        if ($user->hasAnyRole(['Administrador','Rector'])) return true;
        if ($user->hasAnyRole(['Compras','Contabilidad'])) return $r->type !== RequestForm::TYPE_PERMISO;
        if ($user->hasRole('Encargado de departamento')) return $r->department_id && $user->department_id === $r->department_id;
        return $r->user_id === $user->id;
    }
    public function create(User $user): bool { return $user->hasAnyRole(['Administrador','Rector','Encargado de departamento','Usuario']); }
    public function approve(User $user, RequestForm $r): bool {
        if ($user->hasRole('Rector')) return true;
        if ($user->hasRole('Encargado de departamento')) return $r->type === RequestForm::TYPE_PERMISO && $user->department_id === $r->department_id;
        if ($user->hasRole('Compras'))       return $r->type === RequestForm::TYPE_COMPRA;
        if ($user->hasRole('Contabilidad'))  return $r->type === RequestForm::TYPE_CHEQUE;
        return false;
    }
    public function complete(User $user, RequestForm $r): bool { return $user->hasRole('Compras') && $r->type === RequestForm::TYPE_COMPRA; }
}
