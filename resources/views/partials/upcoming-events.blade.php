{{-- resources/views/partials/upcoming-events.blade.php --}}
<div class="bg-white rounded shadow p-4">
  <div class="flex items-center justify-between mb-3">
    <h2 class="text-base font-semibold">Próximos eventos (10 días)</h2>
    <a href="{{ route('calendar.index') }}" class="text-sm text-blue-600 hover:underline">Ver calendario</a>
  </div>

  @if(($upcoming ?? collect())->isEmpty())
    <p class="text-sm text-slate-500">No hay eventos próximos.</p>
  @else
    <ul class="divide-y">
      @foreach($upcoming as $e)
        <li class="py-2">
          <div class="flex items-start gap-3">
            <div class="shrink-0 mt-0.5 text-xs px-2 py-1 rounded bg-slate-100">
              {{ optional($e->start)->timezone(config('app.timezone'))->format('d/M') }}
            </div>
            <div class="flex-1">
              <div class="text-sm font-medium">{{ $e->title }}</div>
              <div class="text-xs text-slate-600">
                @if($e->all_day)
                  Todo el día
                @else
                  {{ optional($e->start)->timezone(config('app.timezone'))->format('H:i') }}
                  @if($e->end)– {{ optional($e->end)->timezone(config('app.timezone'))->format('H:i') }}@endif
                @endif
                @if($e->location) • {{ $e->location }} @endif
              </div>
            </div>
          </div>
        </li>
      @endforeach
    </ul>
  @endif
</div>
