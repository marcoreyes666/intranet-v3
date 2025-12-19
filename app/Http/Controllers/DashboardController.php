<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\RequestForm;
use App\Services\BirthdayService;
use Illuminate\Support\Carbon;
use App\Models\Announcement;
use App\Models\AnnouncementRead;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(BirthdayService $birthdayService)
    {
        $now  = Carbon::now();
        $to   = $now->copy()->addDays(10);
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Próximos eventos + cumpleaños
        |--------------------------------------------------------------------------
        */
        $eventsQuery = Event::query()
            ->where('start', '<', $to)
            ->where(function ($q) use ($now) {
                $q->whereNull('end')->orWhere('end', '>=', $now);
            });

        if (! $user->hasAnyRole(['Administrador', 'Sistemas'])) {
            $eventsQuery->where(function ($q) use ($user) {
                $q->where('is_sound_only', false)
                  ->orWhere(function ($q2) use ($user) {
                      $q2->where('is_sound_only', true)
                         ->where('created_by', $user->id);
                  });
            });
        }

        $events = $eventsQuery
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
            ->toBase();

        $birthdays = $birthdayService
            ->eventsInRange($now, $to)
            ->map(function ($item) {
                return [
                    'type'     => 'birthday',
                    'title'    => "🎂 Cumple: {$item['name']}",
                    'name'     => $item['name'],
                    'start'    => $item['date']->copy()->startOfDay(),
                    'end'      => null,
                    'all_day'  => true,
                    'location' => null,
                ];
            });

        $upcoming = $events->merge($birthdays)
            ->sortBy(fn ($i) => $i['start'])
            ->take(15)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Estadísticas de tickets
        |--------------------------------------------------------------------------
        */
        $statsTickets = [
            'open' => Ticket::visibleTo($user)
                ->whereIn('estado', ['Abierto', 'En proceso'])
                ->count(),

            'mine' => Ticket::where('usuario_id', $user->id)->count(),

            'assigned' => Ticket::where('asignado_id', $user->id)
                ->whereIn('estado', ['Abierto', 'En proceso'])
                ->count(),
        ];

        $myRecentTickets = Ticket::visibleTo($user)
            ->with(['departamento'])
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Solicitudes
        |--------------------------------------------------------------------------
        */
        $pendingToApprove = 0;
        $myRequests       = collect();

        if (class_exists(RequestForm::class)) {
            $myRequests = RequestForm::query()
                ->with(['department'])
                ->where('user_id', $user->id)
                ->latest()
                ->limit(5)
                ->get();

            $isApprover = $user->hasAnyRole([
                'Rector',
                'Compras',
                'Contabilidad',
                'Encargado de departamento',
            ]);

            if ($isApprover) {
                $pendingToApprove = RequestForm::query()
                    ->where('status', 'en_revision')
                    ->whereHas('approvals', function ($q) use ($user) {
                        $q->where('state', 'pendiente')
                          ->where(function ($q2) use ($user) {
                              if ($user->hasRole('Rector')) {
                                  $q2->orWhere('role', 'Rector');
                              }
                              if ($user->hasRole('Compras')) {
                                  $q2->orWhere('role', 'Compras');
                              }
                              if ($user->hasRole('Contabilidad')) {
                                  $q2->orWhere('role', 'Contabilidad');
                              }
                              if ($user->hasRole('Encargado de departamento')) {
                                  $q2->orWhere('role', 'Encargado');
                              }
                          });
                    })
                    ->count();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Notificaciones
        |--------------------------------------------------------------------------
        */
        $unreadNotifications = $user->unreadNotifications()->count();

        /*
        |--------------------------------------------------------------------------
        | Avisos institucionales (para el dashboard)
        |--------------------------------------------------------------------------
        */
        $announcementItems = Announcement::visibleTo($user)
            ->orderByDesc('is_pinned')
            ->latest()
            ->limit(5)
            ->get();

        $announcementReads = AnnouncementRead::where('user_id', $user->id)
            ->pluck('read_at', 'announcement_id');

        return view('dashboard', compact(
            'upcoming',
            'statsTickets',
            'myRecentTickets',
            'pendingToApprove',
            'myRequests',
            'unreadNotifications',
            'announcementItems',
            'announcementReads'
        ));
    }
}
