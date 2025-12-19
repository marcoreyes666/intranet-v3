{{-- resources/views/tickets/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Tickets
            </h2>

            @can('create', \App\Models\Ticket::class)
                <a href="{{ route('tickets.create') }}" class="btn btn-primary">
                    Nuevo Ticket
                </a>
            @endcan
        </div>
    </x-slot>

    {{-- Filtros --}}
    <div class="box bg-white dark:bg-gray-800 p-5 rounded shadow">
        <form method="GET" class="grid grid-cols-12 gap-3">
            <div class="col-span-12 sm:col-span-3">
                <label class="form-label text-sm text-slate-600 dark:text-slate-300">Estado</label>
                <select name="estado" class="form-select mt-1 w-full">
                    <option value="">Todos</option>
                    @foreach (['Abierto', 'En proceso', 'Resuelto', 'Cerrado'] as $e)
                        <option value="{{ $e }}" @selected(request('estado') === $e)>{{ $e }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-12 sm:col-span-3">
                <label class="form-label text-sm text-slate-600 dark:text-slate-300">Categoría</label>
                <select name="categoria" class="form-select mt-1 w-full">
                    <option value="">Todas</option>
                    @foreach (['Sistemas', 'Mantenimiento', 'Redes', 'Impresoras', 'Software', 'Infraestructura'] as $c)
                        <option value="{{ $c }}" @selected(request('categoria') === $c)>{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-12 sm:col-span-3">
                <label class="form-label text-sm text-slate-600 dark:text-slate-300">Prioridad</label>
                <select name="prioridad" class="form-select mt-1 w-full">
                    <option value="">Todas</option>
                    @foreach (['Baja', 'Media', 'Alta', 'Crítica'] as $p)
                        <option value="{{ $p }}" @selected(request('prioridad') === $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-12 sm:col-span-3">
                <label class="form-label text-sm text-slate-600 dark:text-slate-300">Departamento</label>
                <select name="departamento_id" class="form-select mt-1 w-full">
                    <option value="">Todos</option>
                    @foreach ($departamentos as $d)
                        <option value="{{ $d->id }}" @selected(request('departamento_id') == $d->id)>
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-12 flex gap-2 mt-3">
                <button class="btn btn-secondary">
                    Filtrar
                </button>
                <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="bg-white dark:bg-gray-800 rounded shadow mt-5 overflow-x-auto">
        <div class="p-5">
            <table class="table w-full">
                <thead>
                    <tr class="text-left">
                        <th class="py-2 px-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">ID</th>
                        <th class="py-2 px-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Título</th>
                        <th class="py-2 px-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Categoría</th>
                        <th class="py-2 px-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Prioridad</th>
                        <th class="py-2 px-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                        <th class="py-2 px-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Departamento</th>
                        <th class="py-2 px-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Reportante</th>
                        <th class="py-2 px-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">Asignado</th>
                        <th class="py-2 px-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $t)
                        <tr class="border-t border-gray-200 dark:border-gray-700">
                            <td class="py-2 px-2 text-sm">{{ $t->id }}</td>
                            <td class="py-2 px-2 text-sm">{{ $t->titulo }}</td>
                            <td class="py-2 px-2 text-sm">{{ $t->categoria ?? '—' }}</td>
                            <td class="py-2 px-2 text-sm">{{ $t->prioridad ?? '—' }}</td>
                            <td class="py-2 px-2 text-sm">{{ $t->estado ?? '—' }}</td>
                            <td class="py-2 px-2 text-sm">{{ $t->departamento?->name ?? '-' }}</td>
                            <td class="py-2 px-2 text-sm">{{ $t->usuario?->name }}</td>
                            <td class="py-2 px-2 text-sm">{{ $t->asignado?->name ?? '-' }}</td>
                            <td class="py-2 px-2 text-right text-sm">
                                <a href="{{ route('tickets.show', $t) }}" class="text-primary hover:underline">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-slate-500 p-4">
                                Sin registros
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
