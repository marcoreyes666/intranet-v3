<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\Event;

class CalendarController extends Controller
{
    public function fetch(Request $request)
    {
        $startQ = $request->query('start');
        $endQ   = $request->query('end');

        $start = $startQ ? Carbon::parse($startQ) : null;
        $end   = $endQ   ? Carbon::parse($endQ)   : null;

        $events = Event::query()
            ->when($start && $end, function ($q) use ($start, $end) {
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
                    'extendedProps' => [
                        'location'    => $e->location,
                        'description' => $e->description,
                    ],
                ];
            });

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => ['required','string','max:255'],
            'description' => ['nullable','string','max:2000'],
            'location'    => ['nullable','string','max:255'],
            'all_day'     => ['required','boolean'],
            'start'       => ['required','date'],
            'end'         => ['required','date','after_or_equal:start'],
            'color'       => ['nullable','string','max:20'],
        ]);

        $data['created_by'] = Auth::id();

        if ($data['all_day']) {
            $s = Carbon::parse($data['start'])->startOfDay();
            $e = Carbon::parse($data['end'])->startOfDay();
            if ($e->equalTo($s)) { 
                $e = $s->copy()->addDay(); 
            }
            $data['start'] = $s;
            $data['end']   = $e;
        }

        $event = Event::create($data);
        return response()->json($event, 201);
    }
}
