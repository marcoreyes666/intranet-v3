<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Detalle de solicitud de sonido
        </h2>
    </x-slot>

    @php
        $status = $soundRequest->status;
        $statusLabel = $status->label();
    @endphp

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Mensajes flash --}}
            @if (session('success'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Tarjeta principal --}}
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 space-y-4">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold">
                            {{ $soundRequest->event_title ?: 'Sin título de evento' }}
                        </h3>
                        <p class="text-sm text-gray-500">
                            Solicitud #{{ $soundRequest->id }}
                        </p>
                    </div>

                    <div class="text-right space-y-2">
                        <span class="inline-flex px-2 py-1 rounded-full text-xs
                            @switch($status->value)
                                @case('accepted') bg-green-100 text-green-800 @break
                                @case('rejected') bg-red-100 text-red-800 @break
                                @case('returned') bg-yellow-100 text-yellow-800 @break
                                @default          bg-gray-100 text-gray-800
                            @endswitch">
                            {{ $statusLabel }}
                        </span>

                        @if($soundRequest->is_late)
                            <div>
                                <span class="inline-flex px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">
                                    Extemporánea (&lt; 3 días)
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <h4 class="font-semibold mb-1">Datos del evento</h4>
                        <p><span class="font-medium">Fecha:</span>
                            {{ optional($soundRequest->event_date)->format('d/m/Y') }}</p>
                        <p><span class="font-medium">Horario:</span>
                            {{ $soundRequest->start_time }} – {{ $soundRequest->end_time }}</p>
                    </div>

                    <div>
                        <h4 class="font-semibold mb-1">Solicitante</h4>
                        <p class="font-medium">
                            {{ $soundRequest->user->name ?? 'N/A' }}
                        </p>
                        <p class="text-gray-500 text-xs">
                            {{ $soundRequest->user->email ?? '' }}
                        </p>
                    </div>
                </div>

                <div>
                    <h4 class="font-semibold mb-1">Requerimientos de sonido</h4>
                    <p class="text-sm whitespace-pre-line">
                        {{ $soundRequest->requirements }}
                    </p>
                </div>

                @if($soundRequest->review_comment)
                    <div>
                        <h4 class="font-semibold mb-1">Comentario de revisión</h4>
                        <p class="text-sm text-gray-700 whitespace-pre-line">
                            {{ $soundRequest->review_comment }}
                        </p>
                    </div>
                @endif
            </div>

            {{-- Acciones para Sistemas / Admin --}}
            @if ($user->hasAnyRole(['Administrador', 'Sistemas']))
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                    <h4 class="font-semibold mb-4 text-sm">Acciones</h4>
                    <div class="flex flex-wrap gap-3">

                        {{-- Devolver --}}
                        <form method="POST" action="{{ route('sound-requests.return', $soundRequest) }}"
                              class="flex items-center gap-2">
                            @csrf
                            <input type="text" name="review_comment" class="form-control form-control-sm"
                                   placeholder="Motivo de devolución (opcional)">
                            <button type="submit"
                                class="px-3 py-1 text-xs rounded bg-yellow-100 text-yellow-800 hover:bg-yellow-200">
                                Devolver al usuario
                            </button>
                        </form>

                        {{-- Aceptar --}}
                        <form method="POST" action="{{ route('sound-requests.accept', $soundRequest) }}">
                            @csrf
                            <button type="submit"
                                class="px-3 py-1 text-xs rounded bg-green-100 text-green-800 hover:bg-green-200">
                                Aceptar y crear evento
                            </button>
                        </form>

                        {{-- Rechazar --}}
                        <form method="POST" action="{{ route('sound-requests.reject', $soundRequest) }}"
                              class="flex items-center gap-2">
                            @csrf
                            <input type="text" name="review_comment" class="form-control form-control-sm"
                                   placeholder="Motivo de rechazo (opcional)">
                            <button type="submit"
                                class="px-3 py-1 text-xs rounded bg-red-100 text-red-800 hover:bg-red-200">
                                Rechazar
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <div>
                <a href="{{ route('sound-requests.index') }}"
                   class="text-sm text-blue-600 hover:underline">
                    ← Volver al listado
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
