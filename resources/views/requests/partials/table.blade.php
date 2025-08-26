{{-- resources/views/requests/partials/table.blade.php --}}
<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
    <thead>
        <tr class="bg-gray-50 dark:bg-gray-700 text-left text-xs font-semibold uppercase">
            <th class="px-4 py-2">Folio</th>
            <th class="px-4 py-2">Tipo</th>
            <th class="px-4 py-2">Estado</th>
            <th class="px-4 py-2">Documento</th>
            <th class="px-4 py-2">Acciones</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
        @forelse ($items as $rq)
        <tr>
            <td class="px-4 py-2">#{{ $rq->id }}</td>
            <td class="px-4 py-2 capitalize">{{ $rq->type }}</td>
            <td class="px-4 py-2">{{ str_replace('_',' ',$rq->status) }}</td>
            <td class="px-4 py-2">
                @if($rq->documents->count())
                    <a href="{{ route('requests.document',$rq) }}" class="text-primary underline">Descargar</a>
                @else
                    —
                @endif
            </td>
            <td class="px-4 py-2">
                <a href="{{ route('requests.show',$rq) }}" class="text-blue-500 hover:underline">Ver</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">Sin registros</td></tr>
        @endforelse
    </tbody>
</table>

<div class="mt-4">
    {{ $items->links() }}
</div>
