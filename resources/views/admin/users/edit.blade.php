<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar usuario') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.users.update',$user) }}" class="space-y-4">
                    @csrf @method('PUT')

                    <div>
                        <label class="block text-sm font-medium mb-1">Nombre *</label>
                        <input name="name" value="{{ old('name',$user->name) }}" required
                               class="w-full border-gray-300 dark:border-gray-700 rounded">
                        @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Email *</label>
                        <input type="email" name="email" value="{{ old('email',$user->email) }}" required
                               class="w-full border-gray-300 dark:border-gray-700 rounded">
                        @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Contraseña (dejar vacío para no cambiar)</label>
                        <input type="password" name="password"
                               class="w-full border-gray-300 dark:border-gray-700 rounded" autocomplete="new-password">
                        @error('password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Departamento</label>
                            <select name="department_id" class="w-full border-gray-300 dark:border-gray-700 rounded">
                                <option value="">— Sin asignar —</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}" @selected(old('department_id',$user->department_id) == $d->id)>{{ $d->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Rol *</label>
                            <select name="role" required class="w-full border-gray-300 dark:border-gray-700 rounded">
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}" @selected(old('role',$currentRole) == $r->name)>{{ $r->name }}</option>
                                @endforeach
                            </select>
                            @error('role') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="pt-2 flex gap-2">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Actualizar</button>
                        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 border rounded">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
