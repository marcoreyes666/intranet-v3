<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-bold">Nueva solicitud de cheque</h1>
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
            <input type="hidden" name="type" value="cheque">

            <div>
                <label class="block mb-1 font-medium">Concepto</label>
                <input type="text" name="concepto" value="{{ old('concepto') }}"
                       class="w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block mb-1 font-medium">Monto</label>
                <input type="number" step="0.01" name="monto" value="{{ old('monto') }}"
                       class="w-full border rounded px-3 py-2" required>
            </div>

            <button type="submit" class="bg-primary text-white px-4 py-2 rounded">Enviar</button>
        </form>
    </div>
</x-app-layout>
