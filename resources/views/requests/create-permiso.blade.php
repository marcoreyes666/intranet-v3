<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Nueva solicitud de permiso
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        @if ($errors->any())
            <div class="bg-red-50 text-red-700 p-3 rounded mb-4">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('requests.store') }}" class="bg-white dark:bg-gray-800 p-6 rounded shadow space-y-4">
            @csrf
            <input type="hidden" name="type" value="permiso">

            <div>
                <label class="block text-sm font-medium mb-1">Fecha</label>
                <input type="date" name="date" value="{{ old('date') }}" class="form-input w-full">
                @error('date')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Hora de salida (opcional)</label>
                    <input type="time" name="start_time" value="{{ old('start_time') }}" class="form-input w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Hora de regreso (opcional)</label>
                    <input type="time" name="end_time" value="{{ old('end_time') }}" class="form-input w-full">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Motivo (opcional)</label>
                <input type="text" name="reason" value="{{ old('reason') }}" class="form-input w-full" placeholder="Trámite, cita médica, etc.">
                @error('reason')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('requests.index') }}" class="btn btn-secondary">Cancelar</a>
                <button class="btn btn-primary">Enviar a revisión</button>
            </div>
        </form>
    </div>
</x-app-layout>
