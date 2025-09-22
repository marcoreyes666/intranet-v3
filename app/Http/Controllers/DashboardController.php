<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index()
    {
        $now = Carbon::now();
        $to  = $now->copy()->addDays(10);

        // Eventos reales → colección BASE (no Eloquent) para poder hacer merge sin errores
        $events = Event::query()
            ->where('start', '<', $to)
            ->where(function ($q) use ($now) {
                $q->whereNull('end')->orWhere('end', '>=', $now);
            })
            ->orderBy('start')
            ->get(['title', 'start', 'end', 'all_day', 'location'])
            ->map(function ($e) {
                return [
                    'type'     => 'event',
                    'title'    => $e->title,
                    'start'    => $e->start ? $e->start->copy() : null,
                    'end'      => $e->end ? $e->end->copy() : null,
                    'all_day'  => (bool) $e->all_day,
                    'location' => $e->location,
                ];
            })
            ->values()
            ->toBase(); // <-- clave: convierte a Illuminate\Support\Collection

        // Cumpleaños (colección base de arrays)
        $birthdays = $this->birthdayItems($now, $to);

        // Unir, ordenar y limitar
        $upcoming = $events->merge($birthdays)
            ->sortBy(fn ($i) => $i['start'])
            ->take(15)
            ->values();

        return view('dashboard', compact('upcoming'));
    }

    /**
     * Cumpleaños entre $start (incl.) y $end (excl.)
     * Devuelve Illuminate\Support\Collection de arrays.
     */
    private function birthdayItems(Carbon $start, Carbon $end)
    {
        // Meses en el rango
        $months = [];
        $cursor = $start->copy()->startOfDay();
        while ($cursor->lt($end)) {
            $m = (int) $cursor->format('n');
            if (!in_array($m, $months, true)) $months[] = $m;
            $cursor->addDay();
        }

        // Años cubiertos (sin duplicados)
        $endInclusive = $end->copy()->subSecond();
        $years = range($start->year, $endInclusive->year);

        $users = User::select('id', 'name', 'birth_date')
            ->whereNotNull('birth_date')
            ->whereIn(DB::raw('MONTH(birth_date)'), $months)
            ->get();

        $items = [];
        $seen  = [];

        foreach ($users as $u) {
            $month = (int) $u->birth_date->format('n');
            $day   = (int) $u->birth_date->format('j');

            foreach ($years as $year) {
                $lastDay = Carbon::create($year, $month, 1)->endOfMonth()->day;
                $safeDay = min($day, $lastDay);
                $d = Carbon::create($year, $month, $safeDay);

                if ($d->gte($start) && $d->lt($end)) {
                    $key = $u->id . '|' . $d->toDateString();
                    if (isset($seen[$key])) continue; // de-dup
                    $seen[$key] = true;

                    $items[] = [
                        'type'     => 'birthday',
                        'title'    => "🎂 Cumple: {$u->name}",
                        'name'     => $u->name,
                        'start'    => $d->copy()->startOfDay(),
                        'end'      => null,
                        'all_day'  => true,
                        'location' => null,
                    ];
                }
            }
        }

        return collect($items);
    }
}
