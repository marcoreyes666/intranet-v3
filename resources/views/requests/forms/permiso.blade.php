<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-bold">Nueva solicitud de permiso</h1>
    </x-slot>

    <div class="p-6 max-w-xl mx-auto space-y-4">
        {{-- Errores de validación --}}
        @if ($errors->any())
            <div class="rounded border border-red-200 bg-red-50 text-red-800 px-4 py-3">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('requests.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="type" value="permiso">

            <div>
                <label class="block mb-1 font-medium">Fecha</label>
                <input type="date" name="fecha" value="{{ old('fecha') }}"
                       class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block mb-1 font-medium">Hora</label>
                <input type="time" name="hora" value="{{ old('hora') }}"
                       class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block mb-1 font-medium">Motivo</label>
                <textarea name="motivo" rows="4"
                          class="w-full border rounded px-3 py-2" required>{{ old('motivo') }}</textarea>
            </div>

            {{-- Opcionalmente tipo de permiso (medio día, trámite, etc.) --}}
            <div>
                <label class="block mb-1 font-medium">Tipo de permiso (opcional)</label>
                <select name="tipo" class="w-full border rounded px-3 py-2">
                    <option value="">Selecciona…</option>
                    <option value="tramite" {{ old('tipo')==='tramite' ? 'selected' : '' }}>Trámite</option>
                    <option value="mediodia" {{ old('tipo')==='mediodia' ? 'selected' : '' }}>Medio día</option>
                    <option value="personal" {{ old('tipo')==='personal' ? 'selected' : '' }}>Asunto personal</option>
                </select>
            </div>

            <button type="submit" class="bg-primary text-white px-4 py-2 rounded">Enviar</button>
        </form>
    </div>
</x-app-layout>
