<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard
            </h2>

            <div class="text-sm text-gray-500 dark:text-gray-400">
                Hola, {{ auth()->user()->name }}
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- GRID PRINCIPAL DE TARJETAS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                {{-- Tickets abiertos (visibles) --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                    <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Tickets abiertos
                    </div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ $statsTickets['open'] ?? 0 }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Abiertos o en proceso visibles para ti.
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('tickets.index') }}"
                           class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                            Ir a tickets
                        </a>
                    </div>
                </div>

                {{-- Tickets creados por mí --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                    <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Mis tickets registrados
                    </div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ $statsTickets['mine'] ?? 0 }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Tickets que tú creaste.
                    </div>
                </div>

                {{-- Tickets asignados a mí --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                    <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Tickets asignados a mí
                    </div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ $statsTickets['assigned'] ?? 0 }}
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Donde eres responsable directo.
                    </div>
                </div>

                {{-- Notificaciones y solicitudes por aprobar --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 space-y-2">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Notificaciones sin leer
                        </div>
                        <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ $unreadNotifications ?? 0 }}
                        </div>
                        <a href="{{ route('notifications.index') }}"
                           class="mt-1 inline-block text-xs text-blue-600 dark:text-blue-400 hover:underline">
                            Ver bandeja
                        </a>
                    </div>

                    <hr class="border-gray-200 dark:border-gray-700">

                    <div>
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Solicitudes por aprobar
                        </div>
                        <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ $pendingToApprove ?? 0 }}
                        </div>
                        <a href="{{ route('requests.index') }}"
                           class="mt-1 inline-block text-xs text-blue-600 dark:text-blue-400 hover:underline">
                            Ir a solicitudes
                        </a>
                    </div>
                </div>
            </div>

            {{-- FILA: AVISOS + PRÓXIMOS EVENTOS --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Avisos institucionales --}}
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg h-full">
                        <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                Avisos institucionales
                            </h3>
                            <a href="{{ route('announcements.manage') }}"
                               class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                Ver todos
                            </a>
                        </div>
                        <div class="p-4">
                            @include('announcements.feed', [
    'items' => $announcementItems,
    'reads' => $announcementReads,
])

                        </div>
                    </div>
                </div>

                {{-- Próximos eventos (10 días) --}}
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg h-full">
                        <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                Próximos eventos (10 días)
                            </h3>
                            <a href="{{ route('calendar.index') }}"
                               class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                Ver calendario
                            </a>
                        </div>

                        <div class="p-4">
                            @if(($upcoming ?? collect())->isEmpty())
                                <p class="text-sm text-gray-600 dark:text-gray-300">No hay eventos próximos.</p>
                            @else
                                <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach($upcoming as $i)
                                        <li class="py-2">
                                            <div class="flex items-start gap-3">
                                                <div class="shrink-0 mt-0.5 text-xs px-2 py-1 rounded
                                                    {{ $i['type'] === 'birthday'
                                                        ? 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300'
                                                        : 'bg-gray-100 dark:bg-gray-700 dark:text-gray-200' }}">
                                                    {{ optional($i['start'])->timezone(config('app.timezone'))->format('d/M') }}
                                                </div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        @if($i['type'] === 'birthday')
                                                            🎂 Cumple: {{ $i['name'] ?? '' }}
                                                        @else
                                                            {{ $i['title'] }}
                                                        @endif
                                                    </div>
                                                    <div class="text-xs text-gray-600 dark:text-gray-300">
                                                        @if($i['all_day'])
                                                            Todo el día
                                                        @else
                                                            {{ optional($i['start'])->timezone(config('app.timezone'))->format('H:i') }}
                                                            @if($i['end'])
                                                                – {{ optional($i['end'])->timezone(config('app.timezone'))->format('H:i') }}
                                                            @endif
                                                        @endif
                                                        @if(!empty($i['location']))
                                                            • {{ $i['location'] }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- FILA: MIS TICKETS RECIENTES + MIS SOLICITUDES --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Mis tickets recientes --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                            Mis tickets recientes
                        </h3>
                        <a href="{{ route('tickets.index') }}"
                           class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                            Ver todos
                        </a>
                    </div>
                    <div class="p-4">
                        @if(($myRecentTickets ?? collect())->isEmpty())
                            <p class="text-sm text-gray-600 dark:text-gray-300">No hay tickets registrados.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                                        <tr>
                                            <th class="px-2 py-1 text-left">Folio</th>
                                            <th class="px-2 py-1 text-left">Título</th>
                                            <th class="px-2 py-1 text-left">Estado</th>
                                            <th class="px-2 py-1 text-left">Depto</th>
                                            <th class="px-2 py-1 text-right">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($myRecentTickets as $t)
                                            <tr class="border-t border-gray-100 dark:border-gray-700">
                                                <td class="px-2 py-1">#{{ $t->id }}</td>
                                                <td class="px-2 py-1 truncate max-w-[160px]">
                                                    {{ $t->titulo }}
                                                </td>
                                                <td class="px-2 py-1">
                                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] bg-gray-100 dark:bg-gray-700">
                                                        {{ $t->estado }}
                                                    </span>
                                                </td>
                                                <td class="px-2 py-1">
                                                    {{ $t->departamento->name ?? '—' }}
                                                </td>
                                                <td class="px-2 py-1 text-right">
                                                    <a href="{{ route('tickets.show', $t) }}"
                                                       class="text-blue-600 dark:text-blue-400 hover:underline">
                                                        Ver
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Mis solicitudes recientes (permiso / cheque / compra) --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                            Mis solicitudes recientes
                        </h3>
                        <a href="{{ route('requests.index') }}"
                           class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                            Ver todas
                        </a>
                    </div>
                    <div class="p-4">
                        @if(($myRequests ?? collect())->isEmpty())
                            <p class="text-sm text-gray-600 dark:text-gray-300">No has enviado solicitudes.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-2 py-1 text-left">Folio</th>
                                        <th class="px-2 py-1 text-left">Tipo</th>
                                        <th class="px-2 py-1 text-left">Estado</th>
                                        <th class="px-2 py-1 text-left">Depto</th>
                                        <th class="px-2 py-1 text-right">Acciones</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($myRequests as $r)
                                        <tr class="border-t border-gray-100 dark:border-gray-700">
                                            <td class="px-2 py-1">#{{ $r->id }}</td>
                                            <td class="px-2 py-1 capitalize">{{ $r->type }}</td>
                                            <td class="px-2 py-1">
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] bg-gray-100 dark:bg-gray-700 capitalize">
                                                    {{ $r->status }}
                                                </span>
                                            </td>
                                            <td class="px-2 py-1">
                                                {{ $r->department->name ?? '—' }}
                                            </td>
                                            <td class="px-2 py-1 text-right">
                                                <a href="{{ route('requests.show', $r) }}"
                                                   class="text-blue-600 dark:text-blue-400 hover:underline">
                                                    Ver
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
