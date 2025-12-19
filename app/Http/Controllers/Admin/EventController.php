<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query()->with('creator');

        // Filtros opcionales
        if ($search = trim((string) $request->get('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_sound_only')) {
            $query->where('is_sound_only', (bool) $request->boolean('is_sound_only'));
        }

        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        if ($from = $request->get('from')) {
            $query->whereDate('start', '>=', Carbon::parse($from)->toDateString());
        }

        if ($to = $request->get('to')) {
            $query->whereDate('start', '<=', Carbon::parse($to)->toDateString());
        }

        $events = $query
            ->orderByDesc('start')
            ->paginate(20)
            ->appends($request->query());

        $users = User::orderBy('name')->get(['id', 'name']);

        return view('admin.events.index', compact('events', 'users'));
    }

    public function show(Event $event)
    {
        $event->load('creator', 'soundRequest');

        return view('admin.events.show', compact('event'));
    }

    public function destroy(Event $event)
    {
        // Si hay solicitud de sonido asociada, solo le quitamos el vínculo
        if ($event->soundRequest) {
            $event->soundRequest->update([
                'event_id' => null,
            ]);
        }

        $event->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Evento eliminado correctamente.');
    }
}
