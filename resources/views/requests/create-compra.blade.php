<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Nueva solicitud de compra
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto" x-data="purchaseForm()">
        @if ($errors->any())
            <div class="bg-red-50 text-red-700 p-3 rounded mb-4">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('requests.store') }}" class="bg-white dark:bg-gray-800 p-6 rounded shadow space-y-6">
            @csrf
            <input type="hidden" name="type" value="compra">

            <div>
                <label class="block text-sm font-medium mb-1">Justificación</label>
                <textarea name="justification" class="form-textarea w-full" rows="3" required>{{ old('justification') }}</textarea>
                @error('justification')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">URLs (opcional)</label>
                <template x-for="(u,idx) in urls" :key="idx">
                    <div class="flex gap-2 mb-2">
                        <input type="url" class="form-input w-full" :name="`urls[${idx}]`" x-model="urls[idx]" placeholder="https://amazon.com/...">
                        <button type="button" class="btn btn-outline" @click="removeUrl(idx)">–</button>
                    </div>
                </template>
                <button type="button" class="btn btn-secondary" @click="addUrl()">Agregar URL</button>
                @error('urls.*')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Productos</label>
                <div class="overflow-x-auto rounded border">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-3 py-2 text-left">Cantidad</th>
                                <th class="px-3 py-2 text-left">Unidad</th>
                                <th class="px-3 py-2 text-left">Descripción</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(it,idx) in items" :key="idx">
                                <tr class="border-t">
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" class="form-input w-32" :name="`items[${idx}][qty]`" x-model="items[idx].qty" required>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" class="form-input w-40" :name="`items[${idx}][unit]`" x-model="items[idx].unit" placeholder="pieza, caja..." required>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" class="form-input w-full" :name="`items[${idx}][description]`" x-model="items[idx].description" required>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <button type="button" class="btn btn-outline" @click="removeItem(idx)">–</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-secondary mt-3" @click="addItem()">Agregar producto</button>
                @error('items')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                @error('items.*.*')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('requests.index') }}" class="btn btn-secondary">Cancelar</a>
                <button class="btn btn-primary">Enviar a revisión</button>
            </div>
        </form>
    </div>

    {{-- Alpine helpers (si tu app ya lo trae global, puedes omitir este bloque) --}}
    <script>
        function purchaseForm(){
            return {
                urls: {!! json_encode(old('urls', [])) !!},
                items: {!! json_encode(old('items', [ ['qty'=>'','unit'=>'','description'=>''] ])) !!},
                addUrl(){ this.urls.push(''); },
                removeUrl(i){ this.urls.splice(i,1); },
                addItem(){ this.items.push({qty:'',unit:'',description:''}); },
                removeItem(i){ if(this.items.length>1) this.items.splice(i,1); },
            }
        }
    </script>
</x-app-layout>
