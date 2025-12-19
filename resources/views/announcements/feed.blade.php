<div class="space-y-3">
    @php
        /** @var \Illuminate\Support\Collection|\App\Models\Announcement[] $items */
        $items = $items ?? collect();

        /** @var \Illuminate\Support\Collection $reads  (key=announcement_id, value=read_at) */
        $reads = $reads ?? collect();
    @endphp

    @forelse($items as $a)
        @php $unread = ! $reads->has($a->id); @endphp

        <div class="border rounded-lg p-4 {{ $unread ? 'bg-yellow-50' : 'bg-white dark:bg-gray-800' }}">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $a->title }}</h3>

                <div class="text-xs text-slate-500">
                    {{ optional($a->starts_at)->format('d/m/Y H:i') ?? 'Sin inicio' }}

                    @if($a->is_pinned)
                        <span class="ml-2 px-2 py-0.5 text-xs rounded bg-slate-200 dark:bg-slate-700 dark:text-slate-200">
                            Fijado
                        </span>
                    @endif

                    @if($unread)
                        <span class="ml-2 px-2 py-0.5 text-xs rounded bg-amber-200">
                            Nuevo
                        </span>
                    @endif
                </div>
            </div>

            <div class="prose max-w-none mt-2 dark:prose-invert">
                {!! $a->body !!}
            </div>

            <button
                class="mt-2 text-xs underline"
                x-data
                @click.prevent="
                    fetch('{{ route('announcements.read', $a) }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    }).then(() => {
                        $el.closest('div.border')?.classList.remove('bg-yellow-50');
                    })
                "
            >
                Marcar como leído
            </button>
        </div>
    @empty
        <p class="text-slate-500 text-sm">No hay avisos por ahora.</p>
    @endforelse
</div>
