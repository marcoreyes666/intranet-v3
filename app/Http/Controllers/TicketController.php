<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Services\NotifyRecipients;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->authorizeResource(Ticket::class, 'ticket');
    }

    public function index(Request $request)
    {
        $tickets = Ticket::query()
            ->visibleTo($request->user())
            ->with(['usuario','asignado','departamento'])
            ->when($request->filled('estado'), fn($q)=>$q->where('estado',$request->estado))
            ->when($request->filled('categoria'), fn($q)=>$q->where('categoria',$request->categoria))
            ->when($request->filled('prioridad'), fn($q)=>$q->where('prioridad',$request->prioridad))
            ->when($request->filled('departamento_id'), fn($q)=>$q->where('departamento_id',$request->departamento_id))
            ->orderByDesc('id')
            ->paginate(15)
            ->appends($request->query());

        $departamentos = Department::orderBy('name')->get(['id','name']);

        return view('tickets.index', compact('tickets','departamentos'));
    }

    public function create()
    {
        $departamentos = Department::orderBy('name')->get(['id','name']);
        return view('tickets.create', compact('departamentos'));
    }

    public function store(StoreTicketRequest $request, NotifyRecipients $notify)
    {
        $data = $request->safe()->except(['categoria','prioridad','estado','adjuntos','imagenes']);
        $data['usuario_id']      = $request->user()->id;
        $data['departamento_id'] = $data['departamento_id'] ?? $request->user()->department_id;
        // si vienen, úsalo; si no, null
        $data['categoria']       = $request->input('categoria');
        $data['prioridad']       = $request->input('prioridad');
        $data['estado']          = 'Abierto';

        $ticket = Ticket::create($data);

        $this->saveAttachmentsFromRequest($request, $ticket);

        // Notificaciones mínimas
        $notify->onTicketCreatedForDepartment($ticket);
        if ($ticket->asignado_id) $notify->onTicketAssigned($ticket);

        return redirect()->route('tickets.show', $ticket)->with('success','Ticket creado.');
    }

    public function show(Ticket $ticket)
    {
        $user = request()->user();

        $puedeVerInternos = false;
        if ($user) {
            $puedeVerInternos =
                $user->hasAnyRole(['Administrador','Encargado de departamento','Rector']) ||
                ($user->department_id && $user->department_id === $ticket->departamento_id);
        }

        $ticket->load(['usuario','asignado','departamento','attachments']);

        $commentsQuery = $ticket->comments()->with('user')->orderBy('id');
        if (!$puedeVerInternos) $commentsQuery->where('visibility','publico');
        $comments = $commentsQuery->get();

        $tecnicos = User::where('department_id',$ticket->departamento_id)->orderBy('name')->get(['id','name']);
        $departamentos = Department::orderBy('name')->get(['id','name']);

        return view('tickets.show', compact('ticket','tecnicos','departamentos','comments','puedeVerInternos'));
    }

    public function edit(Ticket $ticket)
    {
        return view('tickets.edit', compact('ticket'));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket, NotifyRecipients $notify)
    {
        $ticket->update($request->validated());
        if ($ticket->estado === 'Resuelto' && !$ticket->resuelto_en) {
            $ticket->resuelto_en = Carbon::now();
            $ticket->save();
        }

        $this->saveAttachmentsFromRequest($request, $ticket);

        $notify->onTicketUpdated($ticket, $request->user());

        return redirect()->route('tickets.show', $ticket)->with('success','Ticket actualizado.');
    }

    public function destroy(Ticket $ticket)
    {
        // (opcional) borrar adjuntos físicos con Observer al eliminar cada attachment
        $ticket->delete();
        return redirect()->route('tickets.index')->with('success','Ticket eliminado.');
    }

    // --- Acciones de gestión ---

    public function managementUpdate(Request $request, Ticket $ticket, NotifyRecipients $notify)
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'asignado_id' => [
                'nullable',
                Rule::exists('users','id')->where(fn($q)=>$q->where('department_id',$ticket->departamento_id)),
            ],
            'estado'    => ['required', Rule::in(['Abierto','En proceso','Resuelto','Cerrado'])],
            'categoria' => ['required', Rule::in(['Sistemas','Mantenimiento','Redes','Impresoras','Software','Infraestructura'])],
            'prioridad' => ['required', Rule::in(['Baja','Media','Alta','Crítica'])],
        ]);

        $ticket->fill($validated);

        if ($ticket->estado === 'Resuelto' && !$ticket->resuelto_en) {
            $ticket->resuelto_en = now();
        }

        $ticket->save();

        if ($request->filled('asignado_id')) $notify->onTicketAssigned($ticket);
        $notify->onTicketUpdated($ticket, $request->user());

        return back()->with('success','Cambios guardados.');
    }

    public function setMeta(Request $request, Ticket $ticket, NotifyRecipients $notify)
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'categoria' => ['required', Rule::in(['Sistemas','Mantenimiento','Redes','Impresoras','Software','Infraestructura'])],
            'prioridad' => ['required', Rule::in(['Baja','Media','Alta','Crítica'])],
        ]);

        $ticket->update($validated);
        $notify->onTicketUpdated($ticket, $request->user());

        return back()->with('success','Categoría y prioridad actualizadas.');
    }

    public function asignar(Request $request, Ticket $ticket, NotifyRecipients $notify)
    {
        $this->authorize('update', $ticket);

        $request->validate([
            'asignado_id' => [
                'nullable',
                Rule::exists('users','id')->where(fn($q)=>$q->where('department_id',$ticket->departamento_id)),
            ],
        ], [
            'asignado_id.exists' => 'El usuario seleccionado no pertenece al departamento del ticket.',
        ]);

        $ticket->asignado_id = $request->asignado_id ?: null;
        $ticket->save();

        if ($ticket->asignado_id) $notify->onTicketAssigned($ticket);

        return back()->with('success','Técnico asignado.');
    }

    public function cambiarEstado(Request $request, Ticket $ticket, NotifyRecipients $notify)
    {
        $to = $request->validate([
            'estado' => ['required', Rule::in(['Abierto','En proceso','Resuelto','Cerrado'])],
        ])['estado'];

        $this->authorize('transition', [$ticket, $to]);

        $from = $ticket->estado;
        $ticket->estado = $to;
        if ($to === 'Resuelto' && !$ticket->resuelto_en) $ticket->resuelto_en = now();
        $ticket->save();

        $notify->onTicketUpdated($ticket, $request->user());

        return back()->with('success', "Estado actualizado: {$from} → {$to}");
    }

    public function comentar(Request $request, Ticket $ticket, NotifyRecipients $notify)
    {
        $this->authorize('comment', $ticket);

        $validated = $request->validate([
            'comentario' => ['required','string','max:5000'],
            'visibility' => ['required','in:publico,interno'],
        ]);

        TicketComment::create([
            'ticket_id'  => $ticket->id,
            'user_id'    => $request->user()->id,
            'comentario' => $validated['comentario'],
            'visibility' => $validated['visibility'],
        ]);

        $notify->onTicketUpdated($ticket, $request->user());

        return back()->with('success','Comentario agregado.');
    }

    // --- Helpers ---

    protected function saveAttachmentsFromRequest(Request $request, Ticket $ticket): void
    {
        $files = [];
        if ($request->hasFile('adjuntos'))  $files = array_merge($files, $request->file('adjuntos'));
        if ($request->hasFile('imagenes'))  $files = array_merge($files, $request->file('imagenes'));

        foreach ($files as $file) {
            $path = $file->store("tickets/{$ticket->id}", 'local'); // storage/app/...
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
}
