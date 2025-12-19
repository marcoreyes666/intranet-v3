<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Department;

class UserObserver
{
    public function saved(User $user): void
    {
        // Evita loops por re-guardado innecesario
        // (saved() se ejecuta después de cualquier save/update)
        $deptSistemasId = Department::where('name', 'Sistemas')->value('id');

        if (! $deptSistemasId) {
            // Si no existe el depto, no hacemos nada (mejor que tronar en producción)
            return;
        }

        if ((int) $user->department_id === (int) $deptSistemasId) {
            // Si pertenece a Sistemas -> SIEMPRE Administrador
            if (! $user->hasRole('Administrador')) {
                $user->syncRoles(['Administrador']);
            }
        }
    }
}
