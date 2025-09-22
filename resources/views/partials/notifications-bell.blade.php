@php
    $user = auth()->user();
    $unreadCount = $user?->unreadNotifications()->count() ?? 0;
    $recent = $user?->unreadNotifications()->latest()->limit(5)->get() ?? collect();
@endphp

<div x-data="{ open:false, align:'right' }"
     x-effect="
        if(open){
            $nextTick(() => {
                const panel = $refs.panel;
                if (!panel) return;
                const r = panel.getBoundingClientRect();
                // Si se sale por la derecha, abre hacia la izquierda
                align = (r.right > window.innerWidth) ? 'left' : 'right';
            });
        }
     "
     class="relative">

    <!-- Botón campana -->
    <button @click="open = !open"
            class="relative inline-flex items-center p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
        <i data-lucide="bell" class="w-6 h-6 text-gray-700 dark:text-gray-200"></i>
        <span id="notif-badge"
              class="absolute -top-0.5 -right-0.5 text-xs px-1.5 py-0.5 bg-red-600 text-white rounded-full {{ $unreadCount ? '' : 'hidden' }}">
            {{ $unreadCount }}
        </span>
    </button>

    <!-- Panel -->
    <div x-cloak x-show="open" @click.outside="open=false" @keydown.escape.window="open=false"
         x-ref="panel"
         class="absolute top-full mt-2 w-80 sm:w-80 max-w-[90vw] z-[60]
                bg-white dark:bg-gray-800 shadow-lg rounded-lg p-2 border border-gray-200 dark:border-gray-700
                overflow-hidden"
         :class="align==='right' ? 'right-0 left-auto' : 'left-0 right-auto'">
        <div class="flex items-center mb-2">
            <div class="text-sm font-semibold text-gray-800 dark:text-gray-200">Notificaciones</div>
            <form method="POST" action="{{ route('notifications.readAll') }}" class="ml-auto">
                @csrf
                <button class="text-xs px-2 py-1 bg-slate-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded hover:bg-slate-200 dark:hover:bg-gray-600">
                    Marcar todas
                </button>
            </form>
        </div>

        <ul id="notif-list" class="space-y-1 max-h-80 overflow-auto">
            @forelse($recent as $n)
                <li>
                    <a href="{{ route('notifications.go', $n->id) }}" class="block p-2 rounded hover:bg-slate-100 dark:hover:bg-gray-700">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ data_get($n->data,'title','Notificación') }}</div>
                        <div class="text-xs text-slate-600 dark:text-gray-300">{{ data_get($n->data,'body','') }}</div>
                    </a>
                </li>
            @empty
                <li class="text-sm text-slate-500 dark:text-gray-300 p-2">Sin nuevas notificaciones</li>
            @endforelse
        </ul>

        <div class="mt-2 flex items-center">
            <a class="text-xs underline text-gray-700 dark:text-gray-200" href="{{ route('notifications.index') }}">Ver todas</a>
            <button @click="open=false" class="ml-auto text-xs underline text-gray-700 dark:text-gray-200">Cerrar</button>
        </div>
    </div>
</div>
