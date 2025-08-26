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

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        // Route::resource('tickets', ...) usa {ticket} por defecto
        $this->authorizeResource(Ticket::class, 'ticket');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $q = Ticket::query()
            ->with(['usuario', 'asignado', 'departamento'])
            ->when($request->filled('estado'), fn($qq) => $qq->where('estado', $request->estado))
            ->when($request->filled('categoria'), fn($qq) => $qq->where('categoria', $request->categoria))
            ->when($request->filled('prioridad'), fn($qq) => $qq->where('prioridad', $request->prioridad))
            ->when($request->filled('departamento_id'), fn($qq) => $qq->where('departamento_id', $request->departamento_id))
            ->orderByDesc('id');

        // Usuarios comunes: ven los suyos o donde están asignados
        if ($user && !$user->hasAnyRole(['Administrador', 'Encargado de departamento', 'Rector'])) {
            $q->where(fn($qq) => $qq->where('usuario_id', $user->id)
                ->orWhere('asignado_id', $user->id));
        }

        // Encargado: solo tickets de su depto
        if ($user && $user->hasRole('Encargado de departamento') && $user->department_id) {
            $q->where('departamento_id', $user->department_id);
        }

        $tickets = $q->paginate(15)->withQueryString();
        $departamentos = Department::orderBy('name')->get(['id', 'name']);

        return view('tickets.index', compact('tickets', 'departamentos'));
    }

    public function create()
    {
        $departamentos = Department::orderBy('name')->get(['id', 'name']);
        return view('tickets.create', compact('departamentos'));
    }

    public function store(StoreTicketRequest $request)
    {
        // Tomamos solo lo permitido por el Request y EXCLUIMOS meta/estado por si vienen en el payload
        $data = $request->safe()->except(['categoria', 'prioridad', 'estado']);

        // Seteamos el dueño del ticket
        $data['usuario_id'] = $request->user()->id;

        // Forzamos nulos al crear (DB ya debe permitir NULL en estas columnas)
        $data['categoria'] = null;
        $data['prioridad'] = null;
        $data['estado']    = null;

        // Crear ticket
        $ticket = Ticket::create($data);

        // Adjuntos opcionales al crear (déjalo tal cual lo tengas)
        if ($request->hasFile('imagenes')) {
            $request->validate(['imagenes.*' => 'image|mimes:jpg,jpeg,png,webp,gif|max:5120']);
            foreach ($request->file('imagenes') as $file) {
                $path = $file->store('ticket_attachments/' . $ticket->id, 'public');
                \App\Models\TicketAttachment::create([
                    'ticket_id'       => $ticket->id,
                    'user_id'         => $request->user()->id,
                    'path'            => $path,
                    'nombre_original' => $file->getClientOriginalName(),
                    'mime_type'       => $file->getClientMimeType(),
                    'size'            => $file->getSize(),
                    'visibility'      => 'publico', // quita si tu tabla no tiene esta columna
                ]);
            }
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket creado.');
    }


    public function show(Ticket $ticket)
    {
        $user = request()->user();

        // Puede ver internos: Admin/Rector/Encargado o cualquiera del depto del ticket
        $puedeVerInternos = false;
        if ($user) {
            $puedeVerInternos =
                $user->hasAnyRole(['Administrador', 'Encargado de departamento', 'Rector']) ||
                ($user->department_id && $user->department_id === $ticket->departamento_id);
        }

        $ticket->load(['usuario', 'asignado', 'departamento', 'attachments']);

        $commentsQuery = $ticket->comments()->with('user')->orderBy('id');
        if (!$puedeVerInternos) {
            $commentsQuery->where('visibility', 'publico');
        }
        $comments = $commentsQuery->get();

        // Solo técnicos del depto del ticket
        $tecnicos = User::where('department_id', $ticket->departamento_id)
            ->orderBy('name')->get(['id', 'name']);

        $departamentos = Department::orderBy('name')->get(['id', 'name']);

        return view('tickets.show', compact('ticket', 'tecnicos', 'departamentos', 'comments', 'puedeVerInternos'));
    }

    public function edit(Ticket $ticket)
    {
        return view('tickets.edit', compact('ticket'));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        $ticket->update($request->validated());

        if ($ticket->estado === 'Resuelto' && !$ticket->resuelto_en) {
            $ticket->update(['resuelto_en' => Carbon::now()]);
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket actualizado.');
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Ticket eliminado.');
    }

    // ----- Gestión unificada (encargado/admin) -----
    public function managementUpdate(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $request->validate([
            'asignado_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(
                    fn($q) =>
                    $q->where('department_id', $ticket->departamento_id)
                ),
            ],
            'estado'    => ['required', Rule::in(['Abierto', 'En proceso', 'Resuelto', 'Cerrado'])],
            'categoria' => ['required', Rule::in(['Sistemas', 'Mantenimiento', 'Redes', 'Impresoras', 'Software', 'Infraestructura'])],
            'prioridad' => ['required', Rule::in(['Baja', 'Media', 'Alta', 'Crítica'])],
        ]);

        $ticket->fill([
            'asignado_id' => $request->asignado_id,
            'estado'      => $request->estado,
            'categoria'   => $request->categoria,
            'prioridad'   => $request->prioridad,
        ]);

        if ($ticket->estado === 'Resuelto' && !$ticket->resuelto_en) {
            $ticket->resuelto_en = now();
        }

        $ticket->save();

        return back()->with('success', 'Cambios guardados.');
    }

    // ----- (Opcional: rutas antiguas, por si aún las usas en algún blade) -----
    public function setMeta(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);
        $request->validate([
            'categoria' => ['required', Rule::in(['Sistemas', 'Mantenimiento', 'Redes', 'Impresoras', 'Software', 'Infraestructura'])],
            'prioridad' => ['required', Rule::in(['Baja', 'Media', 'Alta', 'Crítica'])],
        ]);
        $ticket->update($request->only('categoria', 'prioridad'));
        return back()->with('success', 'Categoría y prioridad actualizadas.');
    }

    public function asignar(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);
        $request->validate([
            'asignado_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(
                    fn($q) =>
                    $q->where('department_id', $ticket->departamento_id)
                ),
            ],
        ], [
            'asignado_id.exists' => 'El usuario seleccionado no pertenece al departamento del ticket.',
        ]);
        $ticket->update(['asignado_id' => $request->asignado_id]);
        return back()->with('success', 'Técnico asignado.');
    }

    public function cambiarEstado(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);
        $request->validate(['estado' => 'required|in:Abierto,En proceso,Resuelto,Cerrado']);
        $ticket->estado = $request->estado;
        if ($request->estado === 'Resuelto' && !$ticket->resuelto_en) {
            $ticket->resuelto_en = now();
        }
        $ticket->save();
        return back()->with('success', 'Estado actualizado.');
    }

    // ----- Comentarios -----
    public function comentar(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $request->validate([
            'comentario' => 'required|string',
            'visibility' => 'required|in:publico,interno',
        ]);

        TicketComment::create([
            'ticket_id'  => $ticket->id,
            'user_id'    => $request->user()->id,
            'comentario' => $request->comentario,
            'visibility' => $request->visibility,
        ]);

        return back()->with('success', 'Comentario agregado.');
    }

    // ----- Adjuntos -----
    public function uploadAttachment(Request $request, Ticket $ticket)
    {
        // Solo el solicitante puede adjuntar
        abort_unless($request->user()->id === $ticket->usuario_id, 403);

        $request->validate([
            'imagen' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ]);

        $file = $request->file('imagen');
        $path = $file->store('ticket_attachments/' . $ticket->id, 'public');

        TicketAttachment::create([
            'ticket_id'       => $ticket->id,
            'user_id'         => $request->user()->id,
            'path'            => $path,
            'nombre_original' => $file->getClientOriginalName(),
            'mime_type'       => $file->getClientMimeType(),
            'size'            => $file->getSize(),
            'visibility'      => 'publico',
        ]);

        return back()->with('success', 'Imagen adjuntada.');
    }

    public function deleteAttachment(Request $request, Ticket $ticket, TicketAttachment $attachment)
    {
        $this->authorize('update', $ticket);
        abort_unless($attachment->ticket_id === $ticket->id, 404);

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', 'Adjunto eliminado.');
    }
}
