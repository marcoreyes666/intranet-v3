<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Detalle de evento
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <dl class="grid gap-4 md:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Título
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $event->title }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Lugar
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $event->location }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Inicio
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ optional($event->start)->format('Y-m-d H:i') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Fin
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ optional($event->end)->format('Y-m-d H:i') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Todo el día
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $event->all_day ? 'Sí' : 'No' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Creador
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ optional($event->creator)->name }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Tipo
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            @if($event->is_sound_only)
                                Interno de sonido
                            @else
                                Evento normal
                            @endif
                        </dd>
                    </div>

                    @if($event->soundRequest)
                        <div class="md:col-span-2">
                            <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                                Solicitud de sonido asociada
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                Fecha: {{ $event->soundRequest->event_date }} ·
                                Horario: {{ $event->soundRequest->start_time }} - {{ $event->soundRequest->end_time }} ·
                                Estado: {{ $event->soundRequest->status->value ?? $event->soundRequest->status }}
                            </dd>
                        </div>
                    @endif

                    @if($event->description || $event->notes)
                        <div class="md:col-span-2">
                            <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                                Descripción / notas
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">
                                {{ $event->description ?? $event->notes }}
                            </dd>
                        </div>
                    @endif
                </dl>

                <div class="mt-6 flex justify-between">
                    <a href="{{ route('admin.events.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600
                              rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white
                              dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800">
                        Volver al listado
                    </a>

                    <form action="{{ route('admin.events.destroy', $event) }}"
                          method="POST"
                          onsubmit="return confirm('¿Eliminar este evento?');">
                        @csrf
                        @method('DELETE')
                        <button
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm
                                   font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700
                                   focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Eliminar evento
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
