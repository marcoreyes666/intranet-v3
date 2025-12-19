<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Event;
use App\Models\SoundRequest;
use App\Enums\SoundRequestStatus;
use App\Services\SoundRequestService;
use App\Services\BirthdayService;

class CalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Vista principal del calendario.
     */
    public function index()
    {
        // Quién puede gestionar (crear/editar/eliminar) eventos
        $canManage = auth()->user()->hasAnyRole([
            'Administrador',
            'Sistemas',
            'Encargado de departamento',
            'Rector',
        ]);

        return view('calendar.index', compact('canManage'));
    }

    /**
     * FullCalendar pide eventos por rango; devolvemos eventos + cumpleaños.
     */
    public function fetch(Request $request, BirthdayService $birthdayService)
    {
        $startQ = $request->query('start');
        $endQ   = $request->query('end');

        $start = $startQ ? Carbon::parse($startQ) : Carbon::now()->startOfMonth();
        $end   = $endQ   ? Carbon::parse($endQ)   : (clone $start)->endOfMonth()->addDay();

        $user = $request->user();

        $query = Event::query()
            ->where('start', '<', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('end')->orWhere('end', '>', $start);
            });

        // Filtro de visibilidad
        if (! $user->hasAnyRole(['Administrador', 'Sistemas'])) {
            $query->where(function ($q) use ($user) {
                $q->where('is_sound_only', false) // eventos normales
                    ->orWhere(function ($q2) use ($user) {
                        // eventos internos de sonido SOLO si él es el creador
                        $q2->where('is_sound_only', true)
                            ->where('created_by', $user->id);
                    });
            });
        }

        $events = $query
            ->get()
            ->map(function ($e) {
                return [
                    'id'     => (string) $e->id,
                    'title'  => $e->title,
                    'start'  => optional($e->start)->toIso8601String(),
                    'end'    => optional($e->end)->toIso8601String(),
                    'allDay' => (bool) $e->all_day,
                    'extendedProps' => [
                        'description' => $e->description,
                        'location'    => $e->location,
                        'type'        => 'event',
                    ],
                ];
            })
            ->values()
            ->all();

        // Cumpleaños (vía servicio)
        $birthdayItems = $birthdayService
            ->eventsInRange($start, $end)
            ->map(function ($item) {
                /** @var \Illuminate\Support\Carbon $date */
                $date = $item['date'];

                return [
                    'id'     => "bday:{$item['user_id']}:{$date->format('Y')}",
                    'title'  => "🎂 Cumpleaños: {$item['name']}",
                    'start'  => $date->toDateString(),
                    'allDay' => true,
                    'extendedProps' => [
                        'type'       => 'birthday',
                        'user_id'    => $item['user_id'],
                        'name'       => $item['name'],
                        'birth_date' => $item['birth_date']->format('Y-m-d'),
                    ],
                ];
            })
            ->all();

        return response()->json(array_merge($events, $birthdayItems));
    }

    /**
     * Detalles de un evento para el modal.
     */
    public function show(Event $event)
    {
        return response()->json([
            'event' => [
                'id'          => $event->id,
                'title'       => $event->title,
                'description' => $event->description,
                'start'       => optional($event->start)->format('Y-m-d\TH:i'),
                'end'         => optional($event->end)->format('Y-m-d\TH:i'),
                'all_day'     => (bool) $event->all_day,
                'location'    => $event->location,
                'notes'       => $event->notes ?? null,
            ]
        ]);
    }

    /**
     * Crear evento (solo roles con permiso).
     * Opcionalmente crea una SoundRequest asociada si se marca en el form.
     */
    public function store(Request $request, SoundRequestService $soundService)
    {
        $this->authorizeManage();

        // Validar datos del evento
        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'start'   => 'required|date',
            'end'     => 'nullable|date|after_or_equal:start',
            'all_day' => 'nullable',
            'location' => 'nullable|string|max:255',
            'notes'   => 'nullable|string',

            // Campos de sonido (opcionales)
            'request_sound'      => 'nullable|boolean',
            'sound_requirements' => 'nullable|string',
        ]);

        $start = Carbon::parse($validated['start']);
        $end   = isset($validated['end']) ? Carbon::parse($validated['end']) : null;

        // Evento normal (visible para todos, is_sound_only = false)
        $event = Event::create([
            'title'         => $validated['title'],
            'start'         => $start,
            'end'           => $end,
            'all_day'       => $request->boolean('all_day'),
            'location'      => $validated['location'] ?? null,
            'notes'         => $validated['notes'] ?? null,
            'created_by'    => auth()->id(),
            'department_id' => null,          // ajusta si aplicas deptos aquí
            'is_sound_only' => false,
        ]);

        // Si se marcó "Solicitar sonido" y hay requerimientos => crear SoundRequest
        if ($request->boolean('request_sound') && $request->filled('sound_requirements')) {

            // Si no hay hora de fin, asumimos +1 hora
            if (! $end) {
                $end = $start->copy()->addHour();
            }

            $eventDate = $start->copy()->startOfDay()->toDateString();

            SoundRequest::create([
                'user_id'      => auth()->id(),
                'event_id'     => $event->id,
                'event_title'  => $event->title,
                'event_date'   => $eventDate,
                'start_time'   => $start->format('H:i'),
                'end_time'     => $end->format('H:i'),
                'requirements' => $request->input('sound_requirements'),
                'status'       => SoundRequestStatus::Submitted,
                'is_late'      => $soundService->isLate($eventDate),
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'ok'    => true,
                'event' => $event,
            ]);
        }

        return redirect()
            ->route('calendar.index')
            ->with('success', 'Evento creado correctamente.');
    }

    /**
     * Actualizar evento (solo roles con permiso).
     */
    public function update(Request $request, Event $event)
    {
        $this->authorizeManage();

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start'       => 'required|date',
            'end'         => 'nullable|date|after_or_equal:start',
            'all_day'     => 'sometimes|boolean',
            'location'    => 'nullable|string|max:255',
            'notes'       => 'nullable|string',
        ]);

        $data['all_day'] = (bool) ($data['all_day'] ?? false);

        $event->update($data);

        return response()->json(['ok' => true]);
    }

    /**
     * Eliminar evento (solo roles con permiso).
     */
    public function destroy(Event $event)
    {
        $this->authorizeManage();

        $event->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Verifica permisos de gestión de calendario.
     */
    private function authorizeManage(): void
    {
        abort_unless(
            auth()->user()->hasAnyRole([
                'Administrador',
                'Sistemas',
                'Encargado de departamento',
                'Rector',
            ]),
            403
        );
    }
}
