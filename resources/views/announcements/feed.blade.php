<div class="space-y-3">
  @php
    $user = auth()->user();
    $items = $items ?? \App\Models\Announcement::visibleTo($user)->orderByDesc('is_pinned')->latest()->limit(5)->get();
    $reads = \App\Models\AnnouncementRead::where('user_id',$user->id)->pluck('read_at','announcement_id');
  @endphp

  @forelse($items as $a)
    @php $unread = !$reads->has($a->id); @endphp
    <div class="border rounded-lg p-4 {{ $unread ? 'bg-yellow-50' : 'bg-white' }}">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold">{{ $a->title }}</h3>
        <div class="text-xs text-slate-500">
          {{ optional($a->starts_at)->format('d/m/Y H:i') ?? 'Sin inicio' }}
          @if($a->is_pinned) <span class="ml-2 px-2 py-0.5 text-xs rounded bg-slate-200">Fijado</span> @endif
          @if($unread) <span class="ml-2 px-2 py-0.5 text-xs rounded bg-amber-200">Nuevo</span> @endif
        </div>
      </div>
      <div class="prose max-w-none mt-2">{!! $a->body !!}</div>
      <button
        class="mt-2 text-xs underline"
        x-data
        @click.prevent="fetch('{{ route('announcements.read',$a) }}',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(()=>{$el.closest('div.border').classList.remove('bg-yellow-50')})">
        Marcar como leído
      </button>
    </div>
  @empty
    <p class="text-slate-500 text-sm">No hay avisos por ahora.</p>
  @endforelse
</div>
