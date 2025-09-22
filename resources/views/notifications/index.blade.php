@extends('layouts.app') {{-- o tu layout Midone --}}

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center mb-4">
        <h1 class="text-xl font-semibold">Notificaciones</h1>
        <span class="ml-3 text-sm text-slate-500">No leídas: {{ $unreadCount }}</span>
        <form method="POST" action="{{ route('notifications.readAll') }}" class="ml-auto">
            @csrf
            <button class="px-3 py-1 text-sm bg-slate-100 rounded hover:bg-slate-200">Marcar todas</button>
        </form>
    </div>

    <div class="bg-white shadow rounded divide-y">
        @forelse($all as $n)
            <div class="p-4 {{ $n->read() ? 'opacity-70' : '' }}">
                <div class="flex items-start">
                    <i data-lucide="{{ data_get($n->data,'icon','bell') }}" class="w-5 h-5 mr-3"></i>
                    <div class="flex-1">
                        <div class="font-medium">{{ data_get($n->data,'title') }}</div>
                        <div class="text-sm text-slate-600">{{ data_get($n->data,'body') }}</div>
                        <div class="mt-2 flex gap-2">
                            <a href="{{ route('notifications.go', $n->id) }}" class="text-xs underline">Abrir</a>
                            @if(!$n->read())
                            <form method="POST" action="{{ route('notifications.readOne',$n->id) }}">
                                @csrf
                                <button class="text-xs underline">Marcar leída</button>
                            </form>
                            @endif
                        </div>
                    </div>
                    <div class="text-xs text-slate-500 ml-3">{{ $n->created_at->diffForHumans() }}</div>
                </div>
            </div>
        @empty
            <div class="p-6 text-slate-500">No hay notificaciones.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $all->links() }}</div>
</div>
@endsection
