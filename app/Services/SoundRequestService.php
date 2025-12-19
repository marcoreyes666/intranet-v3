<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class SoundRequestService
{
    /**
     * Determina si una solicitud es extemporánea
     * comparando la fecha del evento (YYYY-MM-DD)
     * contra "hoy + 3 días".
     */
    public function isLate(string $eventDate): bool
    {
        // Fecha límite: hoy + 3 días (solo la parte de fecha)
        $limit = Carbon::now()
            ->addDays(3)
            ->startOfDay()
            ->toDateString(); // formato 'YYYY-MM-DD'

        // Si el evento está antes de ese límite, es extemporáneo
        return $eventDate < $limit;
    }
}
