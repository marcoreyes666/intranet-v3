<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $all = $user->notifications()->latest()->paginate(20);
        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact('all','unreadCount'));
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return back()->with('ok', 'Notificaciones marcadas como leídas');
    }

    public function readOne(Request $request, string $id)
    {
        $n = $request->user()->notifications()->where('id',$id)->firstOrFail();
        $n->markAsRead();
        return back();
    }

    // Redirige al recurso asociado (URL guardada en data['url'])
    public function go(Request $request, string $id)
    {
        $n = $request->user()->notifications()->where('id',$id)->firstOrFail();
        $n->markAsRead();
        $url = data_get($n->data, 'url', route('notifications.index'));
        return redirect()->to($url);
    }
}
