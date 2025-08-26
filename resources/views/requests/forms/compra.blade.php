<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-bold">Nueva solicitud de compra</h1>
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
            <input type="hidden" name="type" value="compra">

            <div>
                <label class="block mb-1 font-medium">Proveedor</label>
                <input type="text" name="proveedor" value="{{ old('proveedor') }}"
                       class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block mb-1 font-medium">Justificación</label>
                <textarea name="justificacion" rows="4"
                          class="w-full border rounded px-3 py-2" required>{{ old('justificacion') }}</textarea>
            </div>

            <div>
                <label class="block mb-1 font-medium">Total estimado</label>
                <input type="number" step="0.01" name="total" value="{{ old('total') }}"
                       class="w-full border rounded px-3 py-2" required>
            </div>

            <button type="submit" class="bg-primary text-white px-4 py-2 rounded">Enviar</button>
        </form>
    </div>
</x-app-layout>
