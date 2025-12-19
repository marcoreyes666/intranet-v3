<?php

namespace App\Policies;

use App\Enums\SoundRequestStatus;
use App\Models\SoundRequest;
use App\Models\User;

class SoundRequestPolicy
{
    /**
     * Determina quién puede actualizar una solicitud de sonido.
     */
    public function update(User $user, SoundRequest $request): bool
    {
        // Sistemas / Administrador pueden editar siempre
        if ($user->hasAnyRole(['Administrador', 'Sistemas'])) {
            return true;
        }

        // Solo el dueño de la solicitud
        if ($request->user_id !== $user->id) {
            return false;
        }

        // Solo en estos estados el usuario puede editar
        return in_array($request->status, [
            SoundRequestStatus::Draft,
            SoundRequestStatus::Submitted,
            SoundRequestStatus::Returned,
        ], true);
    }

    /**
     * (Opcional) Ver detalle. Puedes ajustarlo si lo usas.
     */
    public function view(User $user, SoundRequest $request): bool
    {
        if ($user->hasAnyRole(['Administrador', 'Sistemas'])) {
            return true;
        }

        return $request->user_id === $user->id;
    }
}
