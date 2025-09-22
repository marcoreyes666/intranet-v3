@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
  @if (session('ok'))
    <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-2">
      {{ session('ok') }}
    </div>
  @endif

  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Avisos</h1>
    <a href="{{ route('announcements.create') }}"
       class="px-3 py-2 rounded bg-blue-600 text-white">Nuevo aviso</a>
  </div>

  @if($list->isEmpty())
    <div class="rounded border p-6 text-slate-600">
      No hay avisos aún.
    </div>
  @else
    <div class="overflow-x-auto rounded border">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-100">
          <tr class="text-left">
            <th class="px-3 py-2">Título</th>
            <th class="px-3 py-2">Estado</th>
            <th class="px-3 py-2">Audiencia</th>
            <th class="px-3 py-2">Vigencia</th>
            <th class="px-3 py-2">Autor</th>
            <th class="px-3 py-2 text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach($list as $a)
            <tr class="border-t">
              <td class="px-3 py-2">
                <div class="font-medium">{{ $a->title }}</div>
                @if($a->is_pinned)
                  <span class="text-xs px-2 py-0.5 rounded bg-slate-200 inline-block mt-1">Fijado</span>
                @endif
              </td>
              <td class="px-3 py-2">
                <span class="px-2 py-0.5 rounded text-xs
                  {{ $a->status==='published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                  {{ ucfirst($a->status) }}
                </span>
              </td>
              <td class="px-3 py-2">
                @switch($a->audience)
                  @case('all') Todos @break
                  @case('role') Roles: {{ implode(', ', $a->audience_values ?? []) }} @break
                  @case('department') Depts: {{ implode(', ', $a->audience_values ?? []) }} @break
                @endswitch
              </td>
              <td class="px-3 py-2 text-slate-600">
                {{ optional($a->starts_at)->format('d/m/Y H:i') ?? '—' }}
                —
                {{ optional($a->ends_at)->format('d/m/Y H:i') ?? '—' }}
              </td>
              <td class="px-3 py-2 text-slate-600">
                {{ $a->author?->name ?? '—' }}
              </td>
              <td class="px-3 py-2">
                <div class="flex gap-2 justify-end">
                  <a href="{{ route('announcements.edit',$a) }}" class="px-2 py-1 border rounded">Editar</a>
                  <form method="POST" action="{{ route('announcements.destroy',$a) }}"
                        onsubmit="return confirm('¿Eliminar este aviso?')">
                    @csrf @method('DELETE')
                    <button class="px-2 py-1 border rounded text-red-700">Eliminar</button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      {{ $list->links() }}
    </div>
  @endif
</div>
@endsection
