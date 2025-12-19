@extends('layouts.app')

@section('content')
<style>
  .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1rem;border-radius:.5rem;border:1px solid transparent;cursor:pointer;font-weight:600}
  .btn-primary{background:#1d4ed8;color:#fff}
  .btn-outline-secondary{background:#fff;border-color:#cbd5e1;color:#334155}
  .btn-outline-danger{background:#fff;border-color:#fecaca;color:#dc2626}
  .modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.5)}
  .modal-panel{background:#fff;max-width:34rem;width:92vw;border-radius:.75rem;box-shadow:0 20px 40px rgba(0,0,0,.2)}
  .form-control{width:100%;padding:.6rem .75rem;border:1px solid #e5e7eb;border-radius:.5rem}
  .grid{display:grid}.gap-2{gap:.5rem}.mb-2{margin-bottom:.5rem}.mb-3{margin-bottom:.75rem}.mt-3{margin-top:.75rem}.mr-2{margin-right:.5rem}
  .text-right{text-align:right}.text-lg{font-size:1.125rem}.font-medium{font-weight:600}
</style>

<div class="intro-y box p-5">
  <div class="flex items-center justify-between mb-3" style="display:flex;justify-content:space-between;align-items:center">
    <h2 class="text-lg font-medium">Calendario institucional</h2>
    @if($canManage)
      <button id="btn-new-event" class="btn btn-primary">Nuevo evento</button>
    @endif
  </div>

  <div id="calendar" data-can-manage="{{ $canManage ? '1' : '0' }}"></div>
</div>

{{-- Modal Crear --}}
<div id="modal-create" class="modal hidden">
  <div class="modal-backdrop flex items-center justify-center z-50" style="display:flex;align-items:center;justify-content:center">
    <div class="modal-panel p-5">
      <h3 class="text-lg font-medium mb-3">Nuevo evento</h3>
      <form id="form-create">
        @csrf
        <input type="text" name="title" class="form-control mb-2" placeholder="Título" required>
        <input type="datetime-local" name="start" class="form-control mb-2" required>
        <input type="datetime-local" name="end" class="form-control mb-2">
        <label class="mb-2" style="display:flex;align-items:center;gap:.5rem">
          <input type="checkbox" name="all_day"> Todo el día
        </label>
        <input type="text" name="location" class="form-control mb-2" placeholder="Lugar">
        <textarea name="notes" class="form-control mb-3" placeholder="Notas"></textarea>

        {{-- Bloque de solicitud de sonido --}}
        <div class="mb-3" style="border-top:1px solid #e5e7eb;padding-top:.75rem;margin-top:.75rem">
          <label class="mb-2" style="display:flex;align-items:center;gap:.5rem">
            <input type="checkbox" name="request_sound">
            Solicitar sonido / equipo audiovisual
          </label>
          <textarea name="sound_requirements" class="form-control" rows="3"
            placeholder="Ej. 2 bocinas, 1 micrófono inalámbrico, 1 pantalla, etc."></textarea>
          <div class="text-xs text-slate-500 mt-1">
            Recuerda: la solicitud de sonido debe hacerse al menos con 3 días de anticipación.
          </div>
        </div>

        <div class="text-right">
          <button type="button" class="btn btn-outline-secondary mr-2" data-close>Cancelar</button>
          <button class="btn btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal Ver/Editar --}}
<div id="modal-show" class="modal hidden">
  <div class="modal-backdrop flex items-center justify-center z-50" style="display:flex;align-items:center;justify-content:center">
    <div class="modal-panel p-5">
      <div class="flex justify-between items-center mb-3" style="display:flex;justify-content:space-between;align-items:center">
        <h3 class="text-lg font-medium">Detalles de evento</h3>
        <div class="space-x-2">
          @if($canManage)
            <button id="btn-edit" class="btn btn-outline-secondary">Editar</button>
            <button id="btn-delete" class="btn btn-outline-danger">Eliminar</button>
          @endif
          <button class="btn btn-outline-secondary" data-close>Cerrar</button>
        </div>
      </div>

      <form id="form-show" class="grid gap-2">
        @csrf @method('PUT')
        <input type="hidden" name="id">
        <label>Título</label>
        <input type="text" name="title" class="form-control" disabled>
        <label>Inicio</label>
        <input type="datetime-local" name="start" class="form-control" disabled>
        <label>Fin</label>
        <input type="datetime-local" name="end" class="form-control" disabled>
        <label style="display:flex;align-items:center;gap:.5rem">
          <input type="checkbox" name="all_day" disabled> Todo el día
        </label>
        <label>Lugar</label>
        <input type="text" name="location" class="form-control" disabled>
        <label>Notas</label>
        <textarea name="notes" class="form-control" disabled></textarea>

        @if($canManage)
        <div class="text-right mt-3">
          <button type="button" id="btn-cancel-edit" class="btn btn-outline-secondary mr-2" style="display:none">Cancelar</button>
          <button type="submit" id="btn-save" class="btn btn-primary" style="display:none">Guardar cambios</button>
        </div>
        @endif
      </form>
    </div>
  </div>
</div>
@endsection
