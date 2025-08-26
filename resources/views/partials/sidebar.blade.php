@php
    // Helper simple para "activo"
    function nav_active($pattern)
    {
        return request()->routeIs($pattern)
            ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100'
            : 'text-gray-700 dark:text-gray-300';
    }
@endphp

<aside
    class="hidden md:block w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 min-h-[calc(100vh-4rem)]">
    <nav class="p-3 space-y-1">

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-2 px-3 py-2 rounded transition hover:bg-gray-100 dark:hover:bg-gray-700 {{ nav_active('dashboard') }}"
           @if(request()->routeIs('dashboard')) aria-current="page" @endif>
            <i data-lucide="home" class="w-4 h-4"></i>
            <span>Dashboard</span>
        </a>

        {{-- Tickets --}}
        <a href="{{ route('tickets.index') }}"
           class="flex items-center gap-2 px-3 py-2 rounded transition hover:bg-gray-100 dark:hover:bg-gray-700 {{ nav_active('tickets.*') }}"
           @if(request()->routeIs('tickets.*')) aria-current="page" @endif>
            <i data-lucide="life-buoy" class="w-4 h-4"></i>
            <span>Tickets</span>
        </a>

        {{-- --- Separador visual --- --}}
        <div class="mt-4 mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
            Solicitudes
        </div>

        {{-- Mis solicitudes --}}
        <a href="{{ route('requests.index') }}"
           class="flex items-center gap-2 px-3 py-2 rounded transition hover:bg-gray-100 dark:hover:bg-gray-700 {{ nav_active('requests.index') }}">
            <i data-lucide="list-checks" class="w-4 h-4"></i>
            <span>Mis solicitudes</span>
        </a>

        {{-- Nueva solicitud: Permiso --}}
        <a href="{{ route('requests.create','permiso') }}"
           class="flex items-center gap-2 px-3 py-2 rounded transition hover:bg-gray-100 dark:hover:bg-gray-700 {{ nav_active('requests.create') }}">
            <i data-lucide="badge-check" class="w-4 h-4"></i>
            <span>Nuevo permiso</span>
        </a>

        {{-- Nueva solicitud: Cheque --}}
        <a href="{{ route('requests.create','cheque') }}"
           class="flex items-center gap-2 px-3 py-2 rounded transition hover:bg-gray-100 dark:hover:bg-gray-700 {{ nav_active('requests.create') }}">
            <i data-lucide="banknote" class="w-4 h-4"></i>
            <span>Nuevo cheque</span>
        </a>

        {{-- Nueva solicitud: Compra --}}
        <a href="{{ route('requests.create','compra') }}"
           class="flex items-center gap-2 px-3 py-2 rounded transition hover:bg-gray-100 dark:hover:bg-gray-700 {{ nav_active('requests.create') }}">
            <i data-lucide="shopping-cart" class="w-4 h-4"></i>
            <span>Nueva compra</span>
        </a>

        {{-- Pendientes por aprobar (solo para roles aprobadores) --}}
        @role('Encargado de departamento|Contabilidad|Compras|Rector')
        <a href="{{ route('requests.index') }}?filter=pendientes"
           class="flex items-center gap-2 px-3 py-2 rounded transition hover:bg-gray-100 dark:hover:bg-gray-700 {{ request('filter')==='pendientes' ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100' : 'text-gray-700 dark:text-gray-300' }}">
            <i data-lucide="inbox" class="w-4 h-4"></i>
            <span>Pendientes por aprobar</span>
        </a>
        @endrole

        {{-- Agrega más enlaces aquí --}}
    </nav>
</aside>
