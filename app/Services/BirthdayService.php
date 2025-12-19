<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BirthdayService
{
    /**
     * Devuelve una colección de cumpleaños dentro de [start, end).
     * Cada item:
     *  - user_id
     *  - name
     *  - date       (Carbon de la fecha de cumpleaños en el año correspondiente)
     *  - birth_date (Carbon de la fecha real de nacimiento)
     */
    public function eventsInRange(Carbon $start, Carbon $end): Collection
    {
        // Meses involucrados
        $months = [];
        $cursor = $start->copy()->startOfDay();
        while ($cursor->lt($end)) {
            $m = (int) $cursor->format('n');
            if (! in_array($m, $months, true)) {
                $months[] = $m;
            }
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
            $month = (int) $u->birth_date->format('n');
            $day   = (int) $u->birth_date->format('j');

            foreach ($years as $year) {
                $lastDay = Carbon::create($year, $month, 1)->endOfMonth()->day;
                $safeDay = min($day, $lastDay);
                $d = Carbon::create($year, $month, $safeDay);

                if ($d->gte($start) && $d->lt($end)) {
                    $key = $u->id . '|' . $d->toDateString();
                    if (isset($seen[$key])) {
                        continue;
                    }
                    $seen[$key] = true;

                    $items[] = [
                        'user_id'    => $u->id,
                        'name'       => $u->name,
                        'date'       => $d->copy(),
                        'birth_date' => $u->birth_date->copy(),
                    ];
                }
            }
        }

        return collect($items);
    }
}
