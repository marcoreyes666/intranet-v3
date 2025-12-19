<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Gestión de eventos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filtros --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form method="GET" class="grid gap-4 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Buscar
                        </label>
                        <input type="text"
                            name="q"
                            value="{{ request('q') }}"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600
                                   dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Título, lugar o descripción">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Creador
                        </label>
                        <select name="created_by"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600
                                   dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todos</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" @selected(request('created_by') == $u->id)>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Tipo
                        </label>
                        <select name="is_sound_only"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600
                                   dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Normales + sonido</option>
                            <option value="0" @selected(request('is_sound_only') === '0')>Solo normales</option>
                            <option value="1" @selected(request('is_sound_only') === '1')>Solo internos de sonido</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Desde
                        </label>
                        <input type="date"
                            name="from"
                            value="{{ request('from') }}"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600
                                   dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Hasta
                        </label>
                        <input type="date"
                            name="to"
                            value="{{ request('to') }}"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600
                                   dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="md:col-span-4 flex justify-end gap-3 mt-2">
                        <a href="{{ route('admin.events.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600
                                  rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white
                                  dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800">
                            Limpiar
                        </a>
                        <button
                            class="inline-flex items-center px-4 py-2 border border-transparent
                                   text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600
                                   hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2
                                   focus:ring-indigo-500">
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tabla --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">
                                    Inicio
                                </th>
                                <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">
                                    Título
                                </th>
                                <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">
                                    Lugar
                                </th>
                                <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">
                                    Creador
                                </th>
                                <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">
                                    Tipo
                                </th>
                                <th class="px-3 py-2 text-right font-semibold text-gray-700 dark:text-gray-200">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($events as $event)
                                <tr>
                                    <td class="px-3 py-2 whitespace-nowrap text-gray-800 dark:text-gray-100">
                                        {{ optional($event->start)->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="px-3 py-2 text-gray-800 dark:text-gray-100">
                                        {{ $event->title }}
                                    </td>
                                    <td class="px-3 py-2 text-gray-800 dark:text-gray-100">
                                        {{ $event->location }}
                                    </td>
                                    <td class="px-3 py-2 text-gray-800 dark:text-gray-100">
                                        {{ optional($event->creator)->name }}
                                    </td>
                                    <td class="px-3 py-2">
                                        @if($event->is_sound_only)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                Interno sonido
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                Normal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('admin.events.show', $event) }}"
                                           class="inline-flex items-center px-2 py-1 border border-indigo-600 text-indigo-600
                                                  rounded-md text-xs hover:bg-indigo-50">
                                            Ver
                                        </a>

                                        <form action="{{ route('admin.events.destroy', $event) }}"
                                              method="POST"
                                              class="inline-block"
                                              onsubmit="return confirm('¿Eliminar este evento?');">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                class="inline-flex items-center px-2 py-1 border border-red-600 text-red-600
                                                       rounded-md text-xs hover:bg-red-50 ml-1">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                        No hay eventos con los filtros actuales.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $events->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
