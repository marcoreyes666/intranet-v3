<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Event;
use App\Models\User;

class CalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index()
    {
        // Solo para la vista (oculta/mostrar botones)
        $canManage = auth()->user()->hasRole(['Administrador', 'Encargado de departamento', 'Rector']);
        return view('calendar.index', compact('canManage'));
    }

    // FullCalendar pide eventos por rango; devolvemos eventos + cumpleaños
    public function fetch(Request $request)
    {
        $startQ = $request->query('start');
        $endQ   = $request->query('end');

        $start = $startQ ? Carbon::parse($startQ) : Carbon::now()->startOfMonth();
        $end   = $endQ   ? Carbon::parse($endQ)   : (clone $start)->endOfMonth()->addDay();

        $events = Event::query()
            ->where('start', '<', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('end')->orWhere('end', '>', $start);
            })
            ->get()
            ->map(function ($e) {
                return [
                    'id'     => (string)$e->id,
                    'title'  => $e->title,
                    'start'  => optional($e->start)->toIso8601String(),
                    'end'    => optional($e->end)->toIso8601String(),
                    'allDay' => (bool)$e->all_day,
                    'extendedProps' => [
                        'description' => $e->description,
                        'location'    => $e->location,
                        'type'        => 'event',
                    ],
                ];
            })
            ->values()
            ->all();

        $birthdays = $this->birthdayEvents($start, $end);

        return response()->json(array_merge($events, $birthdays));
    }

    // Detalles para el modal (todos los usuarios pueden ver)
    public function show(Event $event)
    {
        return response()->json([
            'event' => [
                'id'          => $event->id,
                'title'       => $event->title,
                'description' => $event->description,
                'start'       => optional($event->start)->format('Y-m-d\TH:i'),
                'end'         => optional($event->end)->format('Y-m-d\TH:i'),
                'all_day'     => (bool)$event->all_day,
                'location'    => $event->location,
                'notes'       => $event->notes ?? null,
            ]
        ]);
    }

    // Crear (solo roles con permiso)
    public function store(Request $request)
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

        $data['all_day'] = (bool)($data['all_day'] ?? false);

        $event = Event::create($data);

        return response()->json(['ok' => true, 'id' => $event->id], 201);
    }

    // Editar (solo roles con permiso)
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

        $data['all_day'] = (bool)($data['all_day'] ?? false);

        $event->update($data);

        return response()->json(['ok' => true]);
    }

    // Eliminar (solo roles con permiso)
    public function destroy(Event $event)
    {
        $this->authorizeManage();
        $event->delete();
        return response()->json(['ok' => true]);
    }

    private function authorizeManage(): void
    {
        abort_unless(auth()->user()->hasRole(['Administrador','Encargado de departamento','Rector']), 403);
    }

    // Genera eventos allDay de cumpleaños dentro de [start, end)
    private function birthdayEvents(Carbon $start, Carbon $end): array
    {
        $months = [];
        $cursor = $start->copy()->startOfDay();
        while ($cursor->lt($end)) {
            $m = (int)$cursor->format('n');
            if (!in_array($m, $months, true)) $months[] = $m;
            $cursor->addDay();
        }

        $endInclusive = $end->copy()->subSecond();
        $years = range($start->year, $endInclusive->year);

        $users = User::select('id', 'name', 'birth_date')
            ->whereNotNull('birth_date')
            ->whereIn(DB::raw('MONTH(birth_date)'), $months)
            ->get();

        $items = [];
        $seen  = [];

        foreach ($users as $u) {
            $month = (int)$u->birth_date->format('n');
            $day   = (int)$u->birth_date->format('j');

            foreach ($years as $year) {
                $lastDay = Carbon::create($year, $month, 1)->endOfMonth()->day;
                $safeDay = min($day, $lastDay);
                $d = Carbon::create($year, $month, $safeDay);

                if ($d->gte($start) && $d->lt($end)) {
                    $key = $u->id . '|' . $d->toDateString();
                    if (isset($seen[$key])) continue;
                    $seen[$key] = true;

                    $items[] = [
                        'id'     => "bday:{$u->id}:{$d->format('Y')}",
                        'title'  => "🎂 Cumpleaños: {$u->name}",
                        'start'  => $d->toDateString(),
                        'allDay' => true,
                        'extendedProps' => [
                            'type'       => 'birthday',
                            'user_id'    => $u->id,
                            'name'       => $u->name,
                            'birth_date' => $u->birth_date->format('Y-m-d'),
                        ],
                    ];
                }
            }
        }
        return $items;
    }
}
