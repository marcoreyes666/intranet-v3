<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Event;

class CalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index()
    {
        // ← IMPORTANTE: usa hasAnyRole para arrays
        $canManage = auth()->user()->hasAnyRole(['Administrador', 'Encargado de departamento', 'Rector']);
        return view('calendar.index', compact('canManage'));
    }

    // FullCalendar solicita eventos por rango
    public function fetch(Request $request)
    {
        $startQ = $request->query('start');
        $endQ   = $request->query('end');

        $start = $startQ ? Carbon::parse($startQ) : null;
        $end   = $endQ   ? Carbon::parse($endQ)   : null;

        $events = Event::query()
            ->when($start && $end, function ($q) use ($start, $end) {
                // eventos que se traslapan con el rango solicitado
                $q->where('start', '<', $end)
                  ->where(function ($w) use ($start) {
                      $w->whereNull('end')->orWhere('end', '>', $start);
                  });
            })
            ->get()
            ->map(function ($e) {
                return [
                    'id'     => $e->id,
                    'title'  => $e->title,
                    'start'  => optional($e->start)->toIso8601String(),
                    'end'    => optional($e->end)->toIso8601String(),
                    'allDay' => (bool)$e->all_day,
                    'color'  => $e->color,
                    // extendedProps
                    'description' => $e->description,
                    'location'    => $e->location,
                ];
            });

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $this->authorizeManage();

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'location'    => 'nullable|string|max:255',
            'start'       => 'required|date',
            'end'         => 'nullable|date|after_or_equal:start',
            'all_day'     => 'sometimes|boolean',
            'color'       => 'nullable|string|max:20',
        ]);

        $data['user_id'] = $request->user()->id;
        $event = Event::create($data);

        return response()->json(['ok' => true, 'id' => $event->id]);
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeManage();

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'location'    => 'nullable|string|max:255',
            'start'       => 'required|date',
            'end'         => 'nullable|date|after_or_equal:start',
            'all_day'     => 'sometimes|boolean',
            'color'       => 'nullable|string|max:20',
        ]);

        $event->update($data);

        return response()->json(['ok' => true]);
    }

    public function destroy(Event $event)
    {
        $this->authorizeManage();

        $event->delete();
        return response()->json(['ok' => true]);
    }

    private function authorizeManage(): void
    {
        // ← IMPORTANTE: usa hasAnyRole para arrays
        if (!auth()->user()->hasAnyRole(['Administrador','Encargado de departamento','Rector'])) {
            abort(403, 'No autorizado');
        }
    }
}
