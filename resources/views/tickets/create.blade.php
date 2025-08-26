<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Nuevo Ticket</h2>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 p-5 rounded shadow">
        <form method="POST" action="{{ route('tickets.store') }}" class="grid grid-cols-12 gap-4" enctype="multipart/form-data">
            @csrf

            <div class="col-span-12">
                <label class="form-label">Título</label>
                <input type="text" name="titulo" class="form-control" required value="{{ old('titulo') }}">
                @error('titulo') <div class="text-danger mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="col-span-12">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="4">{{ old('descripcion') }}</textarea>
            </div>

            <div class="col-span-12 sm:col-span-4">
                <label class="form-label">Departamento destino</label>
                <select name="departamento_id" class="form-select" required>
                    <option value="">Selecciona un departamento…</option>
                    @foreach ($departamentos as $d)
                        <option value="{{ $d->id }}" @selected(old('departamento_id') == $d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
                @error('departamento_id') <div class="text-danger mt-1">{{ $message }}</div> @enderror
            </div>

            {{-- Adjuntar imágenes al crear --}}
            <div class="col-span-12">
                <label class="form-label">Imágenes (opcional)</label>
                <input type="file" name="imagenes[]" accept="image/*" class="form-control" multiple>
                <div class="text-xs text-slate-500 mt-1">Formatos: jpg, jpeg, png, webp, gif. Máx 5MB c/u.</div>
                @error('imagenes.*') <div class="text-danger mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="col-span-12">
                <button class="btn btn-primary">Enviar ticket</button>
                <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary ml-2">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
