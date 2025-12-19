<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Solicitudes de sonido
        </h2>
    </x-slot>

    @php
        $user = auth()->user();
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-4 p-3 rounded bg-yellow-100 text-yellow-800 text-sm">
                    {{ session('warning') }}
                </div>
            @endif

            <div class="mb-4 flex justify-between items-center">
                <h3 class="text-lg font-semibold">Listado de solicitudes</h3>

                {{-- Todos pueden crear solicitud manual --}}
                <a href="{{ route('sound-requests.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700">
                    Nueva solicitud de sonido
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 text-left">
                                <th class="px-3 py-2">Evento</th>
                                <th class="px-3 py-2">Fecha</th>
                                <th class="px-3 py-2">Horario</th>
                                <th class="px-3 py-2">Requerimientos</th>
                                <th class="px-3 py-2">Estado</th>
                                <th class="px-3 py-2">Extemp.</th>
                                <th class="px-3 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $req)
                                @php
                                    $statusVal = $req->status->value;
                                    $statusLabel = [
                                        'draft'       => 'Borrador',
                                        'submitted'   => 'Enviada',
                                        'under_review'=> 'En revisión',
                                        'returned'    => 'Devuelta',
                                        'accepted'    => 'Aceptada',
                                        'rejected'    => 'Rechazada',
                                        'cancelled'   => 'Cancelada',
                                    ][$statusVal] ?? $statusVal;

                                    $canCancelOwner = $req->user_id === $user->id
                                        && in_array($statusVal, ['draft','submitted','under_review','returned'], true);

                                    $canCancelAdmin = $user->hasAnyRole(['Administrador', 'Sistemas'])
                                        && $statusVal !== 'cancelled';
                                @endphp

                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="px-3 py-2 align-top">
                                        <div class="font-semibold">{{ $req->event_title }}</div>
                                        <div class="text-xs text-gray-500">
                                            Por: {{ $req->user->name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        {{ optional($req->event_date)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-3 py-2 align-top whitespace-nowrap">
                                        {{ substr((string) $req->start_time, 0, 5) }} – {{ substr((string) $req->end_time, 0, 5) }}
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        {{ \Illuminate\Support\Str::limit($req->requirements, 80) }}
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs
                                            @switch($statusVal)
                                                @case('accepted') bg-green-100 text-green-800 @break
                                                @case('rejected') bg-red-100 text-red-800 @break
                                                @case('returned') bg-yellow-100 text-yellow-800 @break
                                                @case('cancelled') bg-gray-300 text-gray-800 @break
                                                @default          bg-gray-100 text-gray-800
                                            @endswitch">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        @if($req->is_late)
                                            <span class="inline-flex px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">
                                                Sí
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-500">No</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 align-top">
                                        <div class="flex flex-wrap gap-2">

                                            {{-- Acciones para Sistemas / Administrador --}}
                                            @if ($user->hasAnyRole(['Administrador', 'Sistemas']))
                                                @if (!in_array($statusVal, ['accepted','rejected','cancelled'], true))
                                                    {{-- Devolver --}}
                                                    <form method="POST" action="{{ route('sound-requests.return', $req) }}">
                                                        @csrf
                                                        <button type="submit"
                                                            class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800 hover:bg-yellow-200">
                                                            Devolver
                                                        </button>
                                                    </form>

                                                    {{-- Aceptar --}}
                                                    <form method="POST" action="{{ route('sound-requests.accept', $req) }}">
                                                        @csrf
                                                        <button type="submit"
                                                            class="px-2 py-1 text-xs rounded bg-green-100 text-green-800 hover:bg-green-200">
                                                            Aceptar
                                                        </button>
                                                    </form>

                                                    {{-- Rechazar --}}
                                                    <form method="POST" action="{{ route('sound-requests.reject', $req) }}">
                                                        @csrf
                                                        <button type="submit"
                                                            class="px-2 py-1 text-xs rounded bg-red-100 text-red-800 hover:bg-red-200">
                                                            Rechazar
                                                        </button>
                                                    </form>
                                                @endif

                                                {{-- Cancelar (admin/sistemas) --}}
                                                @if($canCancelAdmin)
                                                    <form method="POST" action="{{ route('sound-requests.cancel', $req) }}">
                                                        @csrf
                                                        <button type="submit"
                                                            class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800 hover:bg-gray-200">
                                                            Cancelar
                                                        </button>
                                                    </form>
                                                @endif

                                                {{-- Eliminar --}}
                                                <form method="POST" action="{{ route('sound-requests.destroy', $req) }}"
                                                      onsubmit="return confirm('¿Eliminar definitivamente esta solicitud?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="px-2 py-1 text-xs rounded bg-red-200 text-red-900 hover:bg-red-300">
                                                        Eliminar
                                                    </button>
                                                </form>

                                            @else
                                                {{-- Acciones para usuario normal --}}
                                                @can('update', $req)
                                                    @if (!in_array($statusVal, ['accepted','rejected','cancelled'], true))
                                                        <a href="{{ route('sound-requests.edit', $req) }}"
                                                           class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800 hover:bg-gray-200">
                                                            Editar
                                                        </a>
                                                    @endif
                                                @endcan

                                                @if($canCancelOwner)
                                                    <form method="POST" action="{{ route('sound-requests.cancel', $req) }}"
                                                          onsubmit="return confirm('¿Cancelar esta solicitud?');">
                                                        @csrf
                                                        <button type="submit"
                                                            class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800 hover:bg-gray-200">
                                                            Cancelar
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-4 text-center text-gray-500">
                                        No hay solicitudes registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
