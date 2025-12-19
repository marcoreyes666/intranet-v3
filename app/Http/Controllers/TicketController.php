<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketAttachment;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;

use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;

use App\Notifications\NewTicketForDepartmentNotification;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketUpdatedNotification;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ==========================
    // LISTADO
    // ==========================
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Ticket::query()
            ->with(['usuario', 'asignado', 'departamento'])
            ->visibleTo($user);

        if ($estado = $request->get('estado')) {
            $query->where('estado', $estado);
        }

        if ($categoria = $request->get('categoria')) {
            $query->where('categoria', $categoria);
        }

        if ($prioridad = $request->get('prioridad')) {
            $query->where('prioridad', $prioridad);
        }

        if ($departamentoId = $request->get('departamento_id')) {
            $query->where('departamento_id', $departamentoId);
        }

        $tickets = $query->orderByDesc('id')->paginate(20);
        $departamentos = Department::orderBy('name')->get();

        return view('tickets.index', compact('tickets', 'departamentos'));
    }

    // ==========================
    // CREAR
    // ==========================
    public function create()
    {
        $this->authorize('create', Ticket::class);

        $departamentos = Department::orderBy('name')->get();

        return view('tickets.create', compact('departamentos'));
    }

    public function store(StoreTicketRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $ticket = Ticket::create([
            'titulo'          => $data['titulo'],
            'descripcion'     => $data['descripcion'] ?? null,
            'categoria'       => $data['categoria'] ?? null,
            'prioridad'       => $data['prioridad'] ?? 'Media',
            'estado'          => 'Abierto',
            'usuario_id'      => $user->id,
            'asignado_id'     => null,
            'departamento_id' => $data['departamento_id'] ?? null,
        ]);

        $this->handleAttachmentsUpload($request, $ticket);

        // Notifica depto + push campana a destinatarios
        $this->notifyNewTicketForDepartment($ticket);

        // Tiempo real para listados
        event(new \App\Events\TicketUpdated($ticket, 'created'));

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('ok', 'Ticket creado correctamente.');
    }

    // ==========================
    // VER DETALLE
    // ==========================
    public function show(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $user = $request->user();

        $puedeVerInternos = $user->hasAnyRole(['Administrador', 'Encargado de departamento', 'Rector']);

        $commentsQuery = $ticket->comments()
            ->with('user')
            ->orderBy('created_at');

        if (! $puedeVerInternos) {
            $commentsQuery->where('visibility', 'publico');
        }

        $comments = $commentsQuery->get();

        $tecnicos = User::query()
            ->where('department_id', $ticket->departamento_id)
            ->orderBy('name')
            ->get();

        $esAutor    = $user->id === $ticket->usuario_id;
        $esAsignado = $user->id === $ticket->asignado_id;

        $estadosAutorizados = [];
        foreach (['Abierto', 'En proceso', 'Resuelto', 'Cerrado'] as $to) {
            if (Gate::forUser($user)->allows('transition', [$ticket, $to])) {
                $estadosAutorizados[] = $to;
            }
        }

        $puedeGestionarEstado = count($estadosAutorizados) > 0;

        return view('tickets.show', [
            'ticket'              => $ticket->load(['usuario', 'asignado', 'departamento', 'attachments']),
            'comments'            => $comments,
            'tecnicos'            => $tecnicos,
            'puedeVerInternos'    => $puedeVerInternos,
            'esAutor'             => $esAutor,
            'esAsignado'          => $esAsignado,
            'puedeGestionarEstado' => $puedeGestionarEstado,
            'estadosAutorizados'  => $estadosAutorizados,
        ]);
    }

    // ==========================
    // EDITAR BÁSICO
    // ==========================
    public function edit(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $departamentos = Department::orderBy('name')->get();

        return view('tickets.edit', compact('ticket', 'departamentos'));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $actor = $request->user();
        $data  = $request->validated();

        $oldAsignado = $ticket->asignado_id;

        $ticket->fill([
            'titulo'      => $data['titulo']      ?? $ticket->titulo,
            'descripcion' => $data['descripcion'] ?? $ticket->descripcion,
            'prioridad'   => $data['prioridad']   ?? $ticket->prioridad,
            'asignado_id' => $data['asignado_id'] ?? $ticket->asignado_id,
        ]);

        $ticket->save();

        $this->handleAttachmentsUpload($request, $ticket);

        if ($oldAsignado !== $ticket->asignado_id) {
            $this->notifyTicketAssigned($ticket, $actor);
        }

        $this->notifyTicketUpdated($ticket, $actor);

        event(new \App\Events\TicketUpdated($ticket, 'updated'));

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('ok', 'Ticket actualizado.');
    }

    // ==========================
    // ASIGNACIÓN
    // ==========================
    public function asignar(Request $request, Ticket $ticket)
    {
        $this->authorize('assign', $ticket);

        $data = $request->validate([
            'asignado_id' => ['nullable', 'exists:users,id'],
        ]);

        $actor       = $request->user();
        $oldAsignado = $ticket->asignado_id;

        $ticket->asignado_id = $data['asignado_id'] ?? null;
        $ticket->save();

        if ($oldAsignado !== $ticket->asignado_id) {
            $this->notifyTicketAssigned($ticket, $actor);
        }

        $this->notifyTicketUpdated($ticket, $actor);

        event(new \App\Events\TicketUpdated($ticket, 'assigned'));

        return back()->with('ok', 'Asignación actualizada.');
    }

    // ==========================
    // ESTADO
    // ==========================
    public function cambiarEstado(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        $data = $request->validate([
            'estado' => ['required', 'in:Abierto,En proceso,Resuelto,Cerrado'],
        ]);

        $to = $data['estado'];

        if (! Gate::forUser($user)->allows('transition', [$ticket, $to])) {
            return back()->with('error', 'No puedes cambiar el estado a esa opción.');
        }

        $ticket->estado = $to;

        if ($to === 'Resuelto') {
            $ticket->resuelto_en = now();
        }

        $ticket->save();

        $this->notifyTicketUpdated($ticket, $user);

        event(new \App\Events\TicketUpdated($ticket, 'status_changed'));

        return back()->with('ok', 'Estado actualizado.');
    }

    // ==========================
    // METADATOS
    // ==========================
    public function setMeta(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $data = $request->validate([
            'categoria' => ['nullable', 'in:Sistemas,Mantenimiento,Redes,Impresoras,Software,Infraestructura'],
            'prioridad' => ['nullable', 'in:Baja,Media,Alta,Crítica'],
        ]);

        $actor = $request->user();

        if (array_key_exists('categoria', $data)) {
            $ticket->categoria = $data['categoria'];
        }
        if (array_key_exists('prioridad', $data)) {
            $ticket->prioridad = $data['prioridad'];
        }

        $ticket->save();

        $this->notifyTicketUpdated($ticket, $actor);

        event(new \App\Events\TicketUpdated($ticket, 'updated'));

        return back()->with('ok', 'Metadatos actualizados.');
    }

    // ==========================
    // GESTIÓN (ADMIN)
    // ==========================
    public function managementUpdate(Request $request, Ticket $ticket)
    {
        $this->authorize('assign', $ticket);

        $data = $request->validate([
            'asignado_id' => ['nullable', 'exists:users,id'],
            'estado'      => ['required', 'in:Abierto,En proceso,Resuelto,Cerrado'],
            'categoria'   => ['nullable', 'in:Sistemas,Mantenimiento,Redes,Impresoras,Software,Infraestructura'],
            'prioridad'   => ['nullable', 'in:Baja,Media,Alta,Crítica'],
        ]);

        $actor       = $request->user();
        $oldAsignado = $ticket->asignado_id;

        $ticket->asignado_id = $data['asignado_id'] ?? null;
        $ticket->estado      = $data['estado'];
        $ticket->categoria   = $data['categoria'] ?? $ticket->categoria;
        $ticket->prioridad   = $data['prioridad'] ?? $ticket->prioridad;

        if ($ticket->estado === 'Resuelto' && ! $ticket->resuelto_en) {
            $ticket->resuelto_en = now();
        }

        $ticket->save();

        if ($oldAsignado !== $ticket->asignado_id) {
            $this->notifyTicketAssigned($ticket, $actor);
        }

        $this->notifyTicketUpdated($ticket, $actor);

        event(new \App\Events\TicketUpdated($ticket, 'updated'));

        return back()->with('ok', 'Gestión del ticket actualizada.');
    }

    // ==========================
    // COMENTARIOS
    // ==========================
    public function comentar(Request $request, Ticket $ticket)
    {
        $this->authorize('comment', $ticket);

        $data = $request->validate([
            'comentario' => ['required', 'string'],
            'visibility' => ['required', 'in:publico,interno'],
        ]);

        $actor = $request->user();

        if (! $actor->hasAnyRole(['Administrador', 'Encargado de departamento', 'Rector'])) {
            $data['visibility'] = 'publico';
        }

        TicketComment::create([
            'ticket_id'  => $ticket->id,
            'user_id'    => $actor->id,
            'comentario' => $data['comentario'],
            'visibility' => $data['visibility'],
        ]);

        $this->notifyTicketUpdated($ticket, $actor);

        event(new \App\Events\TicketUpdated($ticket, 'commented'));

        return back()->with('ok', 'Comentario agregado.');
    }

    // ==========================
    // ADJUNTOS
    // ==========================
    public function uploadAttachment(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $request->validate([
            'adjuntos.*' => ['file', 'max:5120'],
        ]);

        $this->handleAttachmentsUpload($request, $ticket);

        $this->notifyTicketUpdated($ticket, $request->user());

        event(new \App\Events\TicketUpdated($ticket, 'attachment_changed'));

        return back()->with('ok', 'Adjunto(s) agregado(s).');
    }

    public function deleteAttachment(Request $request, Ticket $ticket, TicketAttachment $attachment)
    {
        $this->authorize('update', $ticket);

        if ($attachment->ticket_id !== $ticket->id) {
            abort(404);
        }

        if ($attachment->path && Storage::disk('public')->exists($attachment->path)) {
            Storage::disk('public')->delete($attachment->path);
        }

        $attachment->delete();

        $this->notifyTicketUpdated($ticket, $request->user());

        event(new \App\Events\TicketUpdated($ticket, 'attachment_changed'));

        return back()->with('ok', 'Adjunto eliminado.');
    }

    // ==========================
    // ELIMINAR
    // ==========================
    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);

        foreach ($ticket->attachments as $a) {
            if ($a->path && Storage::disk('public')->exists($a->path)) {
                Storage::disk('public')->delete($a->path);
            }
            $a->delete();
        }

        $ticket->delete();

        event(new \App\Events\TicketUpdated($ticket, 'deleted'));

        return redirect()
            ->route('tickets.index')
            ->with('ok', 'Ticket eliminado.');
    }

    // ==========================
    // HELPERS PRIVADOS
    // ==========================

    private function handleAttachmentsUpload(Request $request, Ticket $ticket): void
    {
        if (! $request->hasFile('adjuntos')) {
            return;
        }

        foreach ($request->file('adjuntos') as $file) {
            if (! $file->isValid()) {
                continue;
            }

            $path = $file->store("tickets/{$ticket->id}", 'public');

            TicketAttachment::create([
                'ticket_id'       => $ticket->id,
                'user_id'         => $request->user()->id,
                'path'            => $path,
                'nombre_original' => $file->getClientOriginalName(),
                'mime_type'       => $file->getClientMimeType(),
                'size'            => $file->getSize(),
                'visibility'      => 'publico',
            ]);
        }
    }

    private function notifyNewTicketForDepartment(Ticket $ticket): void
    {
        if (! $ticket->departamento_id) {
            return;
        }

        $users = User::query()
            ->where('department_id', $ticket->departamento_id)
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new NewTicketForDepartmentNotification($ticket));
        $this->pushNotifBellForUsers($users);
    }

    private function notifyTicketAssigned(Ticket $ticket, ?User $actor = null): void
    {
        if (! $ticket->asignado_id) {
            return;
        }

        $assignee = User::find($ticket->asignado_id);

        if (! $assignee) {
            return;
        }

        if ($actor && $actor->id === $assignee->id) {
            return;
        }

        $assignee->notify(new TicketAssignedNotification($ticket));
        $this->pushNotifBellForUsers(collect([$assignee]));
    }

    private function notifyTicketUpdated(Ticket $ticket, ?User $actor = null): void
    {
        $recipients = collect([
            User::find($ticket->usuario_id),
            $ticket->asignado_id ? User::find($ticket->asignado_id) : null,
        ])->filter()->unique('id');

        if ($actor) {
            $recipients = $recipients->reject(fn($u) => $u->id === $actor->id);
        }

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new TicketUpdatedNotification($ticket));
        $this->pushNotifBellForUsers($recipients);
    }

    private function pushNotifBellForUsers($users): void
    {
        foreach ($users as $u) {
            $count = $u->unreadNotifications()->count();

            $latest = $u->unreadNotifications()
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
                ->values()
                ->all();

            event(new \App\Events\UserNotificationPushed($u->id, $count, $latest));
        }
    }
}
