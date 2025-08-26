<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-bold">Solicitudes</h1>
    </x-slot>

    <div class="p-6">
        {{-- Tabs --}}
        <div class="flex space-x-4 mb-6 border-b">
            <a href="{{ route('requests.index') }}"
               class="pb-2 {{ $filter !== 'pendientes' ? 'border-b-2 border-primary text-primary' : 'text-gray-500' }}">
               Mis solicitudes
            </a>
            @if($pending)
            <a href="{{ route('requests.index', ['filter'=>'pendientes']) }}"
               class="pb-2 {{ $filter==='pendientes' ? 'border-b-2 border-primary text-primary' : 'text-gray-500' }}">
               Pendientes por aprobar
            </a>
            @endif
        </div>

        @if($filter==='pendientes' && $pending)
            <h2 class="text-lg font-semibold mb-2">Pendientes por aprobar</h2>
            @include('requests.partials.table', ['items'=>$pending])
        @else
            <h2 class="text-lg font-semibold mb-2">Mis solicitudes</h2>
            @include('requests.partials.table', ['items'=>$mine])
        @endif
    </div>
</x-app-layout>
