<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Departamentos') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ session('error') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center gap-2 mb-4">
                    <form method="GET" class="flex-1">
                        <input name="search" value="{{ request('search') }}"
                               class="w-full border-gray-300 dark:border-gray-700 rounded"
                               placeholder="Buscar por nombre, código o slug">
                    </form>

                    <a href="{{ route('admin.departments.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        Nuevo
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="py-2 pr-4">Nombre</th>
                                <th class="py-2 pr-4">Código</th>
                                <th class="py-2 pr-4">Slug</th>
                                <th class="py-2 pr-4">Estado</th>
                                <th class="py-2 pr-0 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($departments as $d)
                                <tr>
                                    <td class="py-2 pr-4">{{ $d->name }}</td>
                                    <td class="py-2 pr-4">{{ $d->code ?? '—' }}</td>
                                    <td class="py-2 pr-4 text-gray-500">{{ $d->slug }}</td>
                                    <td class="py-2 pr-4">
                                        <form action="{{ route('admin.departments.toggle',$d) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button class="px-2 py-1 rounded text-xs
                                                {{ $d->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700' }}">
                                                {{ $d->is_active ? 'Activo' : 'Inactivo' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-2 pr-0">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.departments.edit',$d) }}"
                                               class="px-3 py-1 rounded border text-xs hover:bg-gray-50 dark:hover:bg-gray-700">
                                                Editar
                                            </a>
                                            <form action="{{ route('admin.departments.destroy',$d) }}" method="POST"
                                                  onsubmit="return confirm('¿Eliminar departamento?')">
                                                @csrf @method('DELETE')
                                                <button class="px-3 py-1 rounded border text-xs text-red-700 hover:bg-red-50">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-6 text-center text-gray-500">Sin registros</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $departments->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
