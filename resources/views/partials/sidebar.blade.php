@php
    function nav_active($pattern) {
        return request()->routeIs($pattern)
            ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100'
            : 'text-gray-700 dark:text-gray-300';
    }

    $reqGroupActive = request()->routeIs('requests.*');

    // Inyectados por View Composer (AppServiceProvider)
    $notifCount = $notifCount ?? 0;
    $unreadAnnouncementsCount = $unreadAnnouncementsCount ?? 0;
@endphp

<aside class="hidden md:block w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 min-h-[calc(100vh-4rem)]">
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

        {{-- Avisos (badge de no leídos) --}}
        <a href="{{ route('announcements.feed') }}"
           class="flex items-center gap-2 px-3 py-2 rounded transition hover:bg-gray-100 dark:hover:bg-gray-700 {{ nav_active('announcements.*') }}">
            <i data-lucide="megaphone" class="w-4 h-4"></i>
            <span class="flex items-center">
                Avisos
                @if($unreadAnnouncementsCount)
                    <span class="ml-2 text-xs px-1.5 py-0.5 rounded-full bg-amber-500 text-white">
                        {{ $unreadAnnouncementsCount }}
                    </span>
                @endif
            </span>
        </a>

        {{-- Notificaciones (badge) --}}
        @auth
        <a href="{{ route('notifications.index') }}"
           class="flex items-center gap-2 px-3 py-2 rounded transition hover:bg-gray-100 dark:hover:bg-gray-700 {{ nav_active('notifications.*') }}">
            <i data-lucide="bell" class="w-4 h-4"></i>
            <span class="flex items-center">
                Notificaciones
                @if($notifCount)
                    <span class="ml-2 text-xs px-1.5 py-0.5 rounded-full bg-red-600 text-white">
                        {{ $notifCount }}
                    </span>
                @endif
            </span>
        </a>
        @endauth

        {{-- Calendario --}}
        <a href="{{ route('calendar.index') }}"
           class="flex items-center gap-2 px-3 py-2 rounded transition hover:bg-gray-100 dark:hover:bg-gray-700 {{ nav_active('calendar.*') }}">
            <i data-lucide="calendar" class="w-4 h-4"></i>
            <span>Calendario</span>
        </a>

        {{-- Solicitudes de sonido --}}
        <a href="{{ route('sound-requests.index') }}"
           class="flex items-center gap-2 px-3 py-2 rounded transition hover:bg-gray-100 dark:hover:bg-gray-700 {{ nav_active('sound-requests.*') }}">
            <i data-lucide="speaker" class="w-4 h-4"></i>
            <span>Solicitudes de sonido</span>
        </a>

        {{-- -------------------- --}}
        {{-- Grupo: Solicitudes --}}
        {{-- -------------------- --}}
        <div x-data="{ open: {{ $reqGroupActive ? 'true' : 'false' }} }" class="mt-4">
            <button
                @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-2 rounded transition
                       hover:bg-gray-100 dark:hover:bg-gray-700
                       {{ $reqGroupActive ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100' : 'text-gray-700 dark:text-gray-300' }}"
                aria-haspopup="true"
                :aria-expanded="open"
            >
                <span class="flex items-center gap-2">
                    <i data-lucide="files" class="w-4 h-4"></i>
                    <span>Solicitudes</span>
                </span>
                <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" viewBox="0 0 24 24" fill="none">
                    <path stroke="currentColor" stroke-width="2" d="M6 9l6 6 6-6"/>
                </svg>
            </button>

            <div x-show="open" x-collapse class="mt-1 pl-6 space-y-1">
                <a href="{{ route('requests.index') }}"
                   class="flex items-center gap-2 px-3 py-2 rounded transition hover:bg-gray-100 dark:hover:bg-gray-700 {{ nav_active('requests.index') }}">
                    <i data-lucide="list-checks" class="w-4 h-4"></i>
                    <span>Mis solicitudes</span>
                </a>

                <a href="{{ route('requests.create','permiso') }}"
                   class="flex items-center gap-2 px-3 py-2 rounded transition hover:bg-gray-100 dark:hover:bg-gray-700 {{ nav_active('requests.create') }}">
                    <i data-lucide="badge-check" class="w-4 h-4"></i>
                    <span>Nuevo permiso</span>
                </a>

                <a href="{{ route('requests.create','cheque') }}"
                   class="flex items-center gap-2 px-3 py-2 rounded transition hover:bg-gray-100 dark:hover:bg-gray-700 {{ nav_active('requests.create') }}">
                    <i data-lucide="banknote" class="w-4 h-4"></i>
                    <span>Nuevo cheque</span>
                </a>

                <a href="{{ route('requests.create','compra') }}"
                   class="flex items-center gap-2 px-3 py-2 rounded transition hover:bg-gray-100 dark:hover:bg-gray-700 {{ nav_active('requests.create') }}">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                    <span>Nueva compra</span>
                </a>

                @role('Encargado de departamento|Contabilidad|Compras|Rector')
                <a href="{{ route('requests.index') }}?filter=pendientes"
                   class="flex items-center gap-2 px-3 py-2 rounded transition hover:bg-gray-100 dark:hover:bg-gray-700
                          {{ request('filter')==='pendientes' ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100' : 'text-gray-700 dark:text-gray-300' }}">
                    <i data-lucide="inbox" class="w-4 h-4"></i>
                    <span>Pendientes por aprobar</span>
                </a>
                @endrole
            </div>
        </div>

    </nav>
</aside>
