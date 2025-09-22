@extends('layouts.app')

@section('content')
<style>[x-cloak]{ display:none !important; }</style>
<form method="POST" action="{{ route('announcements.store') }}" class="max-w-3xl mx-auto space-y-4" x-data="{aud:'all'}">
  @csrf
  <h1 class="text-xl font-semibold">Nuevo aviso</h1>

  <input name="title" class="w-full border p-2 rounded" placeholder="Título" required>
  <textarea name="body" class="w-full border p-2 rounded h-40" placeholder="Contenido" required></textarea>

  <div class="grid grid-cols-2 gap-3">
    <div>
      <label class="text-sm">Estado</label>
      <select name="status" class="w-full border p-2 rounded">
        <option value="draft">Borrador</option>
        <option value="published" selected>Publicado</option>
      </select>
    </div>
    <label class="flex items-center gap-2 mt-6">
      <input type="checkbox" name="is_pinned" value="1"> Fijar en el tope
    </label>
  </div>

  <div class="grid gap-3">
    <div>
      <label class="text-sm">Audiencia</label>
      <select name="audience" x-model="aud" class="w-full border p-2 rounded">
        <option value="all">Todos</option>
        <option value="role">Por rol</option>
        <option value="department">Por departamento</option>
      </select>
    </div>

    <div x-show="aud==='role'" x-cloak>
      <label class="text-sm">Selecciona roles</label>
      <select name="audience_values[]" multiple class="w-full border p-2 rounded h-32">
        @foreach($roles as $name => $label)
          <option value="{{ $name }}">{{ $label }}</option>
        @endforeach
      </select>
      <small class="text-slate-500">CTRL/⌘ para varios.</small>
    </div>

    <div x-show="aud==='department'" x-cloak>
      <label class="text-sm">Selecciona departamentos</label>
      <select name="audience_values[]" multiple class="w-full border p-2 rounded h-32">
        @foreach($departments as $id => $label)
          <option value="{{ $id }}">{{ $label }}</option>
        @endforeach
      </select>
      <small class="text-slate-500">CTRL/⌘ para varios.</small>
    </div>
  </div>

  <div class="grid grid-cols-2 gap-3">
    <input type="datetime-local" name="starts_at" class="border p-2 rounded">
    <input type="datetime-local" name="ends_at" class="border p-2 rounded">
  </div>

  <div class="flex gap-2">
    <a href="{{ route('announcements.manage') }}" class="px-4 py-2 border rounded">Cancelar</a>
    <button class="px-4 py-2 bg-blue-600 text-white rounded">Guardar</button>
  </div>
</form>
@endsection
