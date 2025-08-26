<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Ticket #{{ $ticket->id }} — {{ $ticket->titulo }}
        </h2>
    </x-slot>

    <div class="grid grid-cols-12 gap-6">
        {{-- Columna izquierda: detalle, comentarios y adjuntos --}}
        <div class="col-span-12 lg:col-span-8">
            {{-- Detalle --}}
            <div class="bg-white dark:bg-gray-800 p-5 rounded shadow">
                <div class="flex flex-wrap gap-2 items-center">
                    <p><b>Categoría:</b> {{ $ticket->categoria ?? '—' }}</p>
                    <p><b>Prioridad:</b> {{ $ticket->prioridad ?? '—' }}</p>
                    <p><b>Estado:</b> {{ $ticket->estado ?? '—' }}</p>
                    <span class="opacity-50">|</span>
                    <p><b>Departamento:</b> {{ $ticket->departamento?->name ?? '-' }}</p>
                </div>
                <p class="mt-2">{{ $ticket->descripcion ?: 'Sin descripción' }}</p>
                <p class="mt-2">
                    <b>Reportante:</b> {{ $ticket->usuario?->name }}
                    <span class="opacity-50">|</span>
                    <b>Asignado:</b> {{ $ticket->asignado?->name ?? '-' }}
                </p>
            </div>

            {{-- Comentarios --}}
            <div class="bg-white dark:bg-gray-800 p-5 rounded shadow mt-5">
                <h3 class="font-medium">Comentarios</h3>

                <div class="mt-3 space-y-3">
                    @forelse($comments as $c)
                        <div class="p-3 rounded border">
                            <div class="text-slate-500 text-xs">
                                {{ $c->user->name }} · {{ $c->created_at->diffForHumans() }}
                                @if($c->visibility === 'interno')
                                    <span class="ml-2 px-2 py-0.5 text-xs rounded bg-amber-100 text-amber-800">Interno</span>
                                @endif
                            </div>
                            <div class="mt-1">{{ $c->comentario }}</div>
                        </div>
                    @empty
                        <div class="text-slate-500">Aún no hay comentarios</div>
                    @endforelse
                </div>

                {{-- Nuevo comentario --}}
                <form method="POST" action="{{ route('tickets.comentar', $ticket) }}" class="mt-4">
                    @csrf
                    <textarea name="comentario" class="form-control" rows="3" placeholder="Escribe un comentario..."
                        required></textarea>

                    <div class="mt-2 flex items-center gap-4">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="visibility" value="publico" checked>
                            <span>Público</span>
                        </label>

                        @if($puedeVerInternos)
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="visibility" value="interno">
                                <span>Interno</span>
                            </label>
                        @endif
                    </div>

                    <button class="btn btn-secondary mt-2">Agregar comentario</button>
                </form>
            </div>

            {{-- Adjuntos (subir y listar) --}}
            <div class="bg-white dark:bg-gray-800 p-5 rounded shadow mt-5">
                <h3 class="font-medium">Adjuntos</h3>

                {{-- Solo el solicitante puede adjuntar --}}
                @if(auth()->id() === $ticket->usuario_id)
                    <form method="POST" action="{{ route('tickets.attachments.upload', $ticket) }}" class="mt-3"
                        enctype="multipart/form-data">
                        @csrf
                        <label class="form-label">Adjuntar imagen (jpg/png/webp/gif, máx 5MB)</label>
                        <input type="file" name="imagen" accept="image/*" class="form-control" required>
                        <button class="btn btn-outline-primary mt-2">Subir imagen</button>
                    </form>
                @endif

                @if($ticket->attachments->count())
                    <div class="mt-5 grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($ticket->attachments as $a)
                            <div class="border rounded p-2">
                                <a href="{{ asset('storage/' . $a->path) }}" target="_blank" title="{{ $a->nombre_original }}">
                                    <img src="{{ asset('storage/' . $a->path) }}" alt="{{ $a->nombre_original }}"
                                        class="w-full h-32 object-cover rounded">
                                </a>
                                <div class="text-xs mt-1 truncate" title="{{ $a->nombre_original }}">{{ $a->nombre_original }}
                                </div>

                                @can('update', $ticket)
                                    <form method="POST" action="{{ route('tickets.attachments.delete', [$ticket, $a]) }}"
                                        onsubmit="return confirm('¿Eliminar adjunto?')" class="mt-2">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm w-full">Eliminar</button>
                                    </form>
                                @endcan
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-slate-500 mt-3">Sin adjuntos todavía.</div>
                @endif
            </div>
        </div>

        {{-- Columna derecha: gestión unificada --}}
        <div class="col-span-12 lg:col-span-4">
            <div class="bg-white dark:bg-gray-800 p-5 rounded shadow">
                <h3 class="font-medium">Gestión</h3>

                @can('update', $ticket)
                    <form method="POST" action="{{ route('tickets.management.update', $ticket) }}" class="mt-3">
                        @csrf @method('PATCH')

                        <label class="form-label">Asignar a</label>
                        <select name="asignado_id" class="form-select">
                            <option value="">— Sin asignar —</option>
                            @foreach($tecnicos as $t)
                                <option value="{{ $t->id }}" @selected($ticket->asignado_id === $t->id)>{{ $t->name }}</option>
                            @endforeach
                        </select>

                        <label class="form-label mt-3">Estado</label>
                        <select name="estado" class="form-select">
                            @foreach (['Abierto', 'En proceso', 'Resuelto', 'Cerrado'] as $e)
                                <option value="{{ $e }}" @selected($ticket->estado === $e)>{{ $e }}</option>
                            @endforeach
                        </select>

                        <label class="form-label mt-3">Categoría</label>
                        <select name="categoria" class="form-select">
                            @foreach (['Sistemas', 'Mantenimiento', 'Redes', 'Impresoras', 'Software', 'Infraestructura'] as $c)
                                <option value="{{ $c }}" @selected($ticket->categoria === $c)>{{ $c }}</option>
                            @endforeach
                        </select>

                        <label class="form-label mt-3">Prioridad</label>
                        <select name="prioridad" class="form-select">
                            @foreach (['Baja', 'Media', 'Alta', 'Crítica'] as $p)
                                <option value="{{ $p }}" @selected($ticket->prioridad === $p)>{{ $p }}</option>
                            @endforeach
                        </select>

                        <button class="btn btn-primary mt-4 w-full">Guardar cambios</button>
                    </form>
                @endcan

                {{-- Editar / Eliminar (si sigues usando vistas de edición) --}}
                @can('update', $ticket)
                    <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-warning mt-5 w-full">Editar</a>
                @endcan
                @can('delete', $ticket)
                    <form method="POST" action="{{ route('tickets.destroy', $ticket) }}" class="mt-2"
                        onsubmit="return confirm('¿Eliminar ticket?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger w-full">Eliminar</button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>