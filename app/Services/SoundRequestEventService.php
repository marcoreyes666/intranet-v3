<?php

namespace App\Services;

use App\Models\Event;
use App\Models\SoundRequest;
use Illuminate\Support\Carbon;

class SoundRequestEventService
{
    /**
     * Crea un evento o actualiza el existente vinculado a la solicitud de sonido.
     * Siempre marca el evento como "solo sonido" (is_sound_only = true).
     */
    public function createOrAttachEvent(SoundRequest $request): Event
    {
        // Si ya tiene evento asociado → lo actualizamos
        if ($request->event) {
            $event = $request->event;
            $event->update([
                'title'        => $request->event_title,
                'start'        => Carbon::parse($request->event_date . ' ' . $request->start_time),
                'end'          => Carbon::parse($request->event_date . ' ' . $request->end_time),
                'all_day'      => false,
                'description'  => $request->requirements,
                'is_sound_only'=> true,
                'created_by'   => $request->user_id,
            ]);

            return $event;
        }

        // Crear un evento nuevo
        return Event::create([
            'title'         => $request->event_title,
            'start'         => Carbon::parse($request->event_date . ' ' . $request->start_time),
            'end'           => Carbon::parse($request->event_date . ' ' . $request->end_time),
            'all_day'       => false,
            'description'   => $request->requirements,
            'created_by'    => $request->user_id,
            'department_id' => null,
            'is_sound_only' => true,
        ]);
    }
}
