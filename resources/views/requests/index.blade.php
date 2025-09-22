<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Solicitudes</h2>
            <div class="flex gap-2">
                <a href="{{ route('requests.create','permiso') }}" class="btn btn-primary">Nuevo permiso</a>
                <a href="{{ route('requests.create','cheque') }}" class="btn btn-outline">Nuevo cheque</a>
                <a href="{{ route('requests.create','compra') }}" class="btn btn-outline">Nueva compra</a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-5">
        @if (session('ok')) <div class="bg-green-50 text-green-800 p-3 rounded">{{ session('ok') }}</div> @endif

        <form method="GET" class="bg-white dark:bg-gray-800 p-4 rounded shadow grid grid-cols-12 gap-3">
            <div class="col-span-12 sm:col-span-3">
                <label class="block text-sm font-medium mb-1">Estado</label>
                <select name="status" class="form-select w-full">
                    <option value="">Todos</option>
                    @foreach (['borrador','en_revision','aprobada','rechazada','completada'] as $st)
                        <option value="{{ $st }}" @selected(request('status')===$st)>{{ Str::headline($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-12 sm:col-span-2 flex items-end">
                <button class="btn btn-primary w-full">Filtrar</button>
            </div>
        </form>

        <div class="bg-white dark:bg-gray-800 rounded shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-3 py-2 text-left">Folio</th>
                        <th class="px-3 py-2 text-left">Tipo</th>
                        <th class="px-3 py-2 text-left">Solicitante</th>
                        <th class="px-3 py-2 text-left">Depto</th>
                        <th class="px-3 py-2 text-left">Estado</th>
                        <th class="px-3 py-2 text-left">Nivel</th>
                        <th class="px-3 py-2 text-left">Creada</th>
                        <th class="px-3 py-2 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $r)
                        <tr class="border-t">
                            <td class="px-3 py-2">#{{ $r->id }}</td>
                            <td class="px-3 py-2 capitalize">{{ $r->type }}</td>
                            <td class="px-3 py-2">{{ $r->user->name ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $r->department->name ?? '—' }}</td>
                            <td class="px-3 py-2">
                                <span class="text-xs px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 capitalize">{{ $r->status }}</span>
                            </td>
                            <td class="px-3 py-2">{{ $r->current_level }}</td>
                            <td class="px-3 py-2">{{ $r->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-2 text-right">
                                <a href="{{ route('requests.show',$r) }}" class="btn btn-sm btn-primary">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-3 py-6 text-center text-slate-500" colspan="8">Sin resultados</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $requests->withQueryString()->links() }}</div>
    </div>
</x-app-layout>
