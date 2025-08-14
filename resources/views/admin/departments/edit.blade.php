<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar departamento') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.departments.update',$department) }}" class="space-y-4">
                    @csrf @method('PUT')

                    <div>
                        <label class="block text-sm font-medium mb-1">Nombre *</label>
                        <input name="name" value="{{ old('name',$department->name) }}" required
                               class="w-full border-gray-300 dark:border-gray-700 rounded">
                        @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Código</label>
                            <input name="code" value="{{ old('code',$department->code) }}"
                                   class="w-full border-gray-300 dark:border-gray-700 rounded">
                            @error('code') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Slug</label>
                            <input name="slug" value="{{ old('slug',$department->slug) }}"
                                   class="w-full border-gray-300 dark:border-gray-700 rounded">
                            @error('slug') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Descripción</label>
                        <textarea name="description" rows="3"
                                  class="w-full border-gray-300 dark:border-gray-700 rounded">{{ old('description',$department->description) }}</textarea>
                        @error('description') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" class="rounded"
                               {{ $department->is_active ? 'checked' : '' }}>
                        <span>Activo</span>
                    </label>

                    <div class="pt-2 flex gap-2">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Actualizar</button>
                        <a href="{{ route('admin.departments.index') }}"
                           class="px-4 py-2 border rounded">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
