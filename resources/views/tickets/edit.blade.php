<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Editar Ticket #{{ $ticket->id }}</h2>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 p-5 rounded shadow">
        <form method="POST" action="{{ route('tickets.update',$ticket) }}" class="grid grid-cols-12 gap-4">
            @csrf @method('PUT')
            <div class="col-span-12">
                <label class="form-label">Título</label>
                <input type="text" name="titulo" class="form-control" required value="{{ old('titulo',$ticket->titulo) }}">
            </div>
            <div class="col-span-12">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="4">{{ old('descripcion',$ticket->descripcion) }}</textarea>
            </div>
            <div class="col-span-12 sm:col-span-4">
                <label class="form-label">Categoría</label>
                <select name="categoria" class="form-select">
                    @foreach (['Sistemas','Mantenimiento','Redes','Impresoras','Software','Infraestructura'] as $c)
                        <option value="{{ $c }}" @selected(old('categoria',$ticket->categoria)===$c)>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-12 sm:col-span-4">
                <label class="form-label">Prioridad</label>
                <select name="prioridad" class="form-select">
                    @foreach (['Baja','Media','Alta','Crítica'] as $p)
                        <option value="{{ $p }}" @selected(old('prioridad',$ticket->prioridad)===$p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-12 sm:col-span-4">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    @foreach (['Abierto','En proceso','Resuelto','Cerrado'] as $e)
                        <option value="{{ $e }}" @selected(old('estado',$ticket->estado)===$e)>{{ $e }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-12">
                <button class="btn btn-primary">Guardar cambios</button>
                <a href="{{ route('tickets.show',$ticket) }}" class="btn btn-outline-secondary ml-2">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
