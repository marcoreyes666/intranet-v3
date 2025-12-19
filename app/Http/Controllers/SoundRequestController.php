<?php

namespace App\Http\Controllers;

use App\Models\SoundRequest;
use App\Http\Requests\SoundRequestStoreRequest;
use App\Enums\SoundRequestStatus;
use App\Services\SoundRequestEventService;
use App\Services\SoundRequestService;
use Illuminate\Http\Request;

class SoundRequestController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Sistemas / Admin ven todas; usuarios solo las suyas
        $requests = $user->hasAnyRole(['Administrador', 'Sistemas'])
            ? SoundRequest::latest()->paginate(20)
            : SoundRequest::where('user_id', $user->id)->latest()->paginate(20);

        return view('sound_requests.index', compact('requests'));
    }

    public function create()
    {
        return view('sound_requests.create');
    }

    public function store(SoundRequestStoreRequest $request, SoundRequestService $service)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['status']  = SoundRequestStatus::Submitted;

        // Regla de los 3 días centralizada
        $data['is_late'] = $service->isLate($data['event_date']);

        $soundRequest = SoundRequest::create($data);

        $redirect = redirect()
            ->route('sound-requests.index')
            ->with('success', 'Solicitud enviada correctamente.');

        if ($soundRequest->is_late) {
            $redirect->with(
                'warning',
                'ADVERTENCIA IMPORTANTE: Esta solicitud fue enviada con menos de 3 días de anticipación. '
                . 'Será revisada por el Departamento de Sistemas, pero NO hay garantía de aceptación ni de disponibilidad de equipo.'
            );
        }

        return $redirect;
    }

    public function edit(SoundRequest $soundRequest)
    {
        $this->authorize('update', $soundRequest);
        return view('sound_requests.edit', compact('soundRequest'));
    }

    public function update(
        SoundRequestStoreRequest $request,
        SoundRequest $soundRequest,
        SoundRequestService $service
    ) {
        $this->authorize('update', $soundRequest);

        // Evitar editar si ya está finalizada
        if (in_array($soundRequest->status, [
            SoundRequestStatus::Accepted,
            SoundRequestStatus::Rejected,
            SoundRequestStatus::Cancelled,
        ], true)) {
            return back()->with('warning', 'No puedes editar una solicitud ya finalizada.');
        }

        $data = $request->validated();

        // Recalcular is_late por si cambia la fecha (regla centralizada)
        $data['is_late'] = $service->isLate($data['event_date']);

        $soundRequest->update($data);

        return back()->with('success', 'Solicitud actualizada.');
    }

    public function returnToUser(Request $request, SoundRequest $soundRequest)
    {
        $request->validate([
            'review_comment' => 'nullable|string',
        ]);

        $soundRequest->update([
            'status'         => SoundRequestStatus::Returned,
            'review_comment' => $request->input('review_comment'),
        ]);

        return back()->with('success', 'Solicitud devuelta al usuario.');
    }

    public function accept(SoundRequest $soundRequest, SoundRequestEventService $service)
    {
        // Crear o vincular evento
        $event = $service->createOrAttachEvent($soundRequest);

        $soundRequest->update([
            'status'   => SoundRequestStatus::Accepted,
            'event_id' => $event->id,
        ]);

        return back()->with('success', 'Solicitud aceptada y evento creado/actualizado en el calendario.');
    }

    public function reject(Request $request, SoundRequest $soundRequest)
    {
        $request->validate([
            'review_comment' => 'nullable|string',
        ]);

        $soundRequest->update([
            'status'         => SoundRequestStatus::Rejected,
            'review_comment' => $request->input('review_comment'),
        ]);

        return back()->with('success', 'Solicitud rechazada.');
    }

    /**
     * Cancelar solicitud (usuario dueño o Admin/Sistemas).
     */
    public function cancel(Request $request, SoundRequest $soundRequest)
    {
        $user = $request->user();

        // Solo dueño o Admin/Sistemas
        if (! ($user->id === $soundRequest->user_id || $user->hasAnyRole(['Administrador', 'Sistemas']))) {
            abort(403);
        }

        if ($soundRequest->status === SoundRequestStatus::Cancelled) {
            return back()->with('warning', 'La solicitud ya está cancelada.');
        }

        // Si tiene evento asociado y ES un evento interno de sonido, lo eliminamos
        if ($soundRequest->event && $soundRequest->event->is_sound_only) {
            $soundRequest->event->delete();
        }

        $soundRequest->update([
            'status'         => SoundRequestStatus::Cancelled,
            'review_comment' => $request->input('review_comment'),
        ]);

        return back()->with('success', 'Solicitud cancelada.');
    }

    /**
     * Eliminar solicitud (solo Admin/Sistemas).
     */
    public function destroy(Request $request, SoundRequest $soundRequest)
    {
        $user = $request->user();

        if (! $user->hasAnyRole(['Administrador', 'Sistemas'])) {
            abort(403);
        }

        // Si tiene evento interno de sonido, borrarlo también
        if ($soundRequest->event && $soundRequest->event->is_sound_only) {
            $soundRequest->event->delete();
        }

        $soundRequest->delete();

        return redirect()
            ->route('sound-requests.index')
            ->with('success', 'Solicitud eliminada.');
    }
}
