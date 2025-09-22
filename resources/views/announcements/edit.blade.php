@extends('layouts.app')

@section('content')
<style>[x-cloak]{ display:none !important; }</style>
<form method="POST" action="{{ route('announcements.update',$a) }}" class="max-w-3xl mx-auto space-y-4" x-data="{aud:'{{ $a->audience }}'}">
  @csrf
  @method('PUT')
  <h1 class="text-xl font-semibold">Editar aviso</h1>

  <input name="title" class="w-full border p-2 rounded" value="{{ $a->title }}" required>
  <textarea name="body" class="w-full border p-2 rounded h-40" required>{{ $a->body }}</textarea>

  <div class="grid grid-cols-2 gap-3">
    <div>
      <label class="text-sm">Estado</label>
      <select name="status" class="w-full border p-2 rounded">
        <option value="draft" {{ $a->status==='draft'?'selected':'' }}>Borrador</option>
        <option value="published" {{ $a->status==='published'?'selected':'' }}>Publicado</option>
      </select>
    </div>
    <label class="flex items-center gap-2 mt-6">
      <input type="checkbox" name="is_pinned" value="1" {{ $a->is_pinned ? 'checked' : '' }}> Fijar en el tope
    </label>
  </div>

  <div class="grid gap-3">
    <div>
      <label class="text-sm">Audiencia</label>
      <select name="audience" x-model="aud" class="w-full border p-2 rounded">
        <option value="all" {{ $a->audience==='all'?'selected':'' }}>Todos</option>
        <option value="role" {{ $a->audience==='role'?'selected':'' }}>Por rol</option>
        <option value="department" {{ $a->audience==='department'?'selected':'' }}>Por departamento</option>
      </select>
    </div>

    <div x-show="aud==='role'" x-cloak>
      <label class="text-sm">Selecciona roles</label>
      @php $vals = collect($a->audience_values ?? []); @endphp
      <select name="audience_values[]" multiple class="w-full border p-2 rounded h-32">
        @foreach($roles as $name => $label)
          <option value="{{ $name }}" {{ $vals->contains($name) ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
    </div>

    <div x-show="aud==='department'" x-cloak>
      <label class="text-sm">Selecciona departamentos</label>
      @php $vals = collect($a->audience_values ?? []); @endphp
      <select name="audience_values[]" multiple class="w-full border p-2 rounded h-32">
        @foreach($departments as $id => $label)
          <option value="{{ $id }}" {{ $vals->contains((int)$id) ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div class="grid grid-cols-2 gap-3">
    <input type="datetime-local" name="starts_at" class="border p-2 rounded"
           value="{{ optional($a->starts_at)->format('Y-m-d\TH:i') }}">
    <input type="datetime-local" name="ends_at" class="border p-2 rounded"
           value="{{ optional($a->ends_at)->format('Y-m-d\TH:i') }}">
  </div>

  <div class="flex gap-2">
    <a href="{{ route('announcements.manage') }}" class="px-4 py-2 border rounded">Cancelar</a>
    <button class="px-4 py-2 bg-blue-600 text-white rounded">Actualizar</button>
  </div>
</form>
@endsection
