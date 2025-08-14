<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Usuarios') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('success') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <form method="GET" class="flex-1 max-w-sm">
                        <input name="search" value="{{ request('search') }}" placeholder="Buscar nombre o correo"
                               class="w-full border-gray-300 dark:border-gray-700 rounded">
                    </form>
                    <a href="{{ route('admin.users.create') }}"
                       class="ml-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        Nuevo
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="py-2 pr-4">Nombre</th>
                                <th class="py-2 pr-4">Email</th>
                                <th class="py-2 pr-4">Departamento</th>
                                <th class="py-2 pr-4">Rol</th>
                                <th class="py-2 pr-0 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($users as $u)
                                <tr>
                                    <td class="py-2 pr-4">{{ $u->name }}</td>
                                    <td class="py-2 pr-4">{{ $u->email }}</td>
                                    <td class="py-2 pr-4">{{ $u->department?->name ?? '—' }}</td>
                                    <td class="py-2 pr-4">{{ $u->roles->pluck('name')->join(', ') ?: '—' }}</td>
                                    <td class="py-2 pr-0">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.users.edit',$u) }}"
                                               class="px-3 py-1 rounded border text-xs hover:bg-gray-50 dark:hover:bg-gray-700">
                                                Editar
                                            </a>
                                            <form action="{{ route('admin.users.destroy',$u) }}" method="POST"
                                                  onsubmit="return confirm('¿Eliminar usuario?')">
                                                @csrf @method('DELETE')
                                                <button class="px-3 py-1 rounded border text-xs text-red-700 hover:bg-red-50">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @if($users->isEmpty())
                                <tr><td colspan="5" class="py-6 text-center text-gray-500">Sin registros</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
