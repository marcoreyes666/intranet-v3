<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Notificaciones
            </h2>

            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-500">
                    No leídas: {{ $unreadCount }}
                </span>

                <form method="POST" action="{{ route('notifications.readAll') }}">
                    @csrf
                    <button type="submit"
                        class="px-3 py-1 text-xs rounded bg-slate-100 hover:bg-slate-200
                               dark:bg-slate-700 dark:hover:bg-slate-600">
                        Marcar todas como leídas
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-4">
        @if(session('ok'))
            <div class="bg-green-50 text-green-800 px-3 py-2 rounded text-sm">
                {{ session('ok') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow rounded divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($notifications as $n)
                @php
                    $isRead = ! is_null($n->read_at);
                    $data   = $n->data ?? [];

                    // Detectar tipo de recurso asociado
                    $meta = $data['meta'] ?? [];

                    if (isset($meta['ticket_id'])) {
                        $kind = 'ticket';
                    } elseif (isset($meta['request_id'])) {
                        $kind = 'request';
                    } elseif (isset($meta['announcement_id'])) {
                        $kind = 'announcement';
                    } else {
                        $kind = 'general';
                    }

                    // Estilos por tipo
                    $pillLabel = match($kind) {
                        'ticket'       => 'Ticket',
                        'request'      => 'Solicitud',
                        'announcement' => 'Aviso',
                        default        => 'General',
                    };

                    $pillClasses = match($kind) {
                        'ticket'       => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
                        'request'      => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
                        'announcement' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                        default        => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
                    };

                    // Icono
                    $icon = $data['icon'] ?? 'bell';

                    // Request status (para badge pequeño)
                    $status = $meta['status'] ?? null;
                @endphp

                <div class="p-4
                            {{ $isRead ? 'opacity-70' : '' }}
                            {{ $isRead ? '' : 'bg-slate-50 dark:bg-slate-900/40 border-l-4 border-l-blue-400' }}">
                    <div class="flex items-start gap-3">
                        <div class="mt-1 flex-shrink-0">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                         bg-slate-100 dark:bg-slate-700">
                                <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                            </span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $pillClasses }}">
                                    {{ $pillLabel }}
                                </span>

                                @if($kind === 'request' && $status)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium
                                                 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200">
                                        Estado: {{ $status }}
                                    </span>
                                @endif

                                @if (! $isRead)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium
                                                 bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200">
                                        Nuevo
                                    </span>
                                @endif
                            </div>

                            <div class="mt-1 font-medium text-sm text-gray-900 dark:text-gray-100 truncate">
                                {{ $data['title'] ?? 'Notificación' }}
                            </div>

                            <div class="text-sm text-slate-600 dark:text-slate-300">
                                {{ $data['body'] ?? '' }}
                            </div>

                            <div class="mt-2 flex items-center gap-3 text-xs">
                                <a href="{{ route('notifications.go', $n->id) }}"
                                   class="underline text-blue-600 dark:text-blue-400">
                                    Abrir
                                </a>

                                @unless($isRead)
                                    <form method="POST" action="{{ route('notifications.readOne', $n->id) }}">
                                        @csrf
                                        <button type="submit" class="underline text-slate-500">
                                            Marcar como leída
                                        </button>
                                    </form>
                                @endunless
                            </div>
                        </div>

                        <div class="text-xs text-slate-500 ml-3 whitespace-nowrap">
                            {{ $n->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-slate-500 text-sm">
                    No hay notificaciones.
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    </div>
</x-app-layout>
