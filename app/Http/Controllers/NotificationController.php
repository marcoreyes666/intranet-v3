<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Listado de notificaciones del usuario.
     * Al abrir la bandeja, se marcan todas las no leídas como leídas.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Cuántas no leídas había antes de abrir la bandeja
        $unreadCount = $user->unreadNotifications()->count();

        // Marcar todas como leídas
        $user->unreadNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Obtener todas las notificaciones paginadas
        $notifications = $user->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Marca todas las notificaciones como leídas (botón extra).
     */
    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('ok', 'Notificaciones marcadas como leídas');
    }

    /**
     * Marca una notificación concreta como leída.
     */
    public function readOne(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return back();
    }

    /**
     * Marca como leída y redirige al recurso asociado en data['url'].
     */
    public function go(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        $url = data_get($notification->data, 'url', route('notifications.index'));

        return redirect()->to($url);
    }

    public function summary(\Illuminate\Http\Request $request)
    {
        $user = $request->user();

        $unreadCount = $user->unreadNotifications()->count();

        $latest = $user->unreadNotifications()
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($n) {
                return [
                    'id'    => $n->id,
                    'title' => data_get($n->data, 'title', 'Notificación'),
                    'body'  => data_get($n->data, 'body', ''),
                    'url'   => route('notifications.go', $n->id),
                ];
            })
            ->values();

        return response()->json([
            'unreadCount' => $unreadCount,
            'latest'      => $latest,
        ]);
    }
}
