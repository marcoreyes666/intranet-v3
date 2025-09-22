<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Nueva solicitud de cheque
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        @if ($errors->any())
            <div class="bg-red-50 text-red-700 p-3 rounded mb-4">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('requests.store') }}" class="bg-white dark:bg-gray-800 p-6 rounded shadow space-y-4">
            @csrf
            <input type="hidden" name="type" value="cheque">

            <div>
                <label class="block text-sm font-medium mb-1">A favor de</label>
                <input type="text" name="pay_to" value="{{ old('pay_to') }}" class="form-input w-full" required>
                @error('pay_to')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Concepto de pago</label>
                <textarea name="concept" class="form-textarea w-full" rows="3" required>{{ old('concept') }}</textarea>
                @error('concept')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Moneda</label>
                    <select name="currency" class="form-select w-full">
                        <option value="MXN" @selected(old('currency')==='MXN')>MXN</option>
                        <option value="USD" @selected(old('currency')==='USD')>USD</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Importe</label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" class="form-input w-full" required>
                    @error('amount')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('requests.index') }}" class="btn btn-secondary">Cancelar</a>
                <button class="btn btn-primary">Enviar a revisión</button>
            </div>
        </form>
    </div>
</x-app-layout>
