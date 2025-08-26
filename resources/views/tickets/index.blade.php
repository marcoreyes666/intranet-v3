<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Tickets</h2>
            {{-- BOTÓN CREAR --}}
            <a href="{{ route('tickets.create') }}" class="btn btn-primary">
                Nuevo Ticket
            </a>
        </div>
    </x-slot>

    {{-- Filtros --}}
    <div class="box bg-white dark:bg-gray-800 p-5 rounded shadow">
        <form method="GET" class="grid grid-cols-12 gap-3">
            <select name="estado" class="form-select col-span-12 sm:col-span-3">
                <option value="">Estado</option>
                @foreach (['Abierto', 'En proceso', 'Resuelto', 'Cerrado'] as $e)
                    <option value="{{ $e }}" @selected(request('estado') === $e)>{{ $e }}</option>
                @endforeach
            </select>

            <select name="categoria" class="form-select col-span-12 sm:col-span-3">
                <option value="">Categoría</option>
                @foreach (['Sistemas', 'Mantenimiento', 'Redes', 'Impresoras', 'Software', 'Infraestructura'] as $c)
                    <option value="{{ $c }}" @selected(request('categoria') === $c)>{{ $c }}</option>
                @endforeach
            </select>

            <select name="prioridad" class="form-select col-span-12 sm:col-span-3">
                <option value="">Prioridad</option>
                @foreach (['Baja', 'Media', 'Alta', 'Crítica'] as $p)
                    <option value="{{ $p }}" @selected(request('prioridad') === $p)>{{ $p }}</option>
                @endforeach
            </select>

            <select name="departamento_id" class="form-select col-span-12 sm:col-span-3">
                <option value="">Departamento</option>
                @foreach ($departamentos as $d)
                    <option value="{{ $d->id }}" @selected(request('departamento_id') == $d->id)>{{ $d->name }}</option>
                @endforeach
            </select>

            <div class="col-span-12 flex gap-2">
                <button class="btn btn-secondary">Filtrar</button>
                <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">Limpiar</a>
            </div>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="bg-white dark:bg-gray-800 rounded shadow mt-5 overflow-x-auto">
        <div class="p-5">
            <table class="table w-full">
                <thead>
                    <tr class="text-left">
                        <th>ID</th>
                        <th>Título</th>
                        <th>Categoría</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Departamento</th>
                        <th>Reportante</th>
                        <th>Asignado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $t)
                        <tr class="border-t border-gray-200 dark:border-gray-700">
                            <td>{{ $t->id }}</td>
                            <td>{{ $t->titulo }}</td>
                            <td>{{ $t->categoria ?? '—' }}</td>
                            <td>{{ $t->prioridad ?? '—' }}</td>
                            <td>{{ $t->estado ?? '—' }}</td>
                            <td>{{ $t->departamento?->name ?? '-' }}</td>
                            <td>{{ $t->usuario?->name }}</td>
                            <td>{{ $t->asignado?->name ?? '-' }}</td>
                            <td class="text-right">
                                <a href="{{ route('tickets.show', $t) }}" class="text-primary">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-slate-500 p-4">Sin registros</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">{{ $tickets->links() }}</div>
        </div>
    </div>
</x-app-layout>