@csrf

<div class="grid grid-cols-12 gap-4 gap-y-5">
    {{-- Título del evento --}}
    <div class="col-span-12 md:col-span-6">
        <label class="form-label">Título del evento (opcional)</label>
        <input
            type="text"
            name="event_title"
            class="form-control"
            value="{{ old('event_title', $soundRequest->event_title ?? '') }}"
        >
    </div>

    {{-- Fecha del evento --}}
    <div class="col-span-12 md:col-span-4">
        <label class="form-label">Fecha del evento</label>
        <input
            type="date"
            name="event_date"
            class="form-control"
            value="{{ old('event_date',
                isset($soundRequest) && $soundRequest->event_date
                    ? $soundRequest->event_date->format('Y-m-d')
                    : ''
            ) }}"
            required
        >
    </div>

    {{-- Hora inicio --}}
    <div class="col-span-6 md:col-span-2">
        <label class="form-label">Hora inicio</label>
        <input
            type="time"
            name="start_time"
            class="form-control"
            value="{{ old('start_time',
                isset($soundRequest) && $soundRequest->start_time
                    ? substr((string) $soundRequest->start_time, 0, 5)
                    : ''
            ) }}"
            required
        >
    </div>

    {{-- Hora fin --}}
    <div class="col-span-6 md:col-span-2">
        <label class="form-label">Hora fin</label>
        <input
            type="time"
            name="end_time"
            class="form-control"
            value="{{ old('end_time',
                isset($soundRequest) && $soundRequest->end_time
                    ? substr((string) $soundRequest->end_time, 0, 5)
                    : ''
            ) }}"
            required
        >
    </div>

    {{-- ADVERTENCIA EN VIVO POR FECHA EXTEMPORÁNEA --}}
    <div class="col-span-12">
        <div id="late-warning-live"
             class="hidden mb-2 p-3 rounded border border-red-400 bg-red-50 text-red-800 text-sm font-semibold">
            ⚠️ <strong>ADVERTENCIA IMPORTANTE:</strong> Estás solicitando sonido con menos de
            <strong>3 días de anticipación</strong>. La solicitud será valorada por el Departamento de Sistemas,
            pero <span class="underline">NO hay garantía de aceptación ni de disponibilidad de equipo</span>.
        </div>
    </div>

    {{-- Requerimientos de sonido --}}
    <div class="col-span-12">
        <label class="form-label">Requerimientos de sonido</label>
        <textarea
            name="requirements"
            class="form-control"
            rows="4"
            required
        >{{ old('requirements', $soundRequest->requirements ?? '') }}</textarea>
        <div class="form-help">
            Ejemplo: 2 bocinas, 1 micrófono inalámbrico, 1 pantalla, etc.
        </div>
    </div>

    @if($errors->any())
        <div class="col-span-12">
            <div class="alert alert-danger mt-2">
                <ul class="mb-0 text-xs">
                    @foreach($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>

<div class="flex justify-end mt-5">
    <a href="{{ route('sound-requests.index') }}" class="btn btn-outline-secondary w-24 mr-2">
        Cancelar
    </a>
    <button type="submit" class="btn btn-primary w-32">
        Guardar
    </button>
</div>

{{-- Script simple para mostrar la advertencia en vivo --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputDate = document.querySelector('input[name="event_date"]');
    const box = document.getElementById('late-warning-live');
    if (!inputDate || !box) return;

    const checkLate = () => {
        if (!inputDate.value) {
            box.classList.add('hidden');
            return;
        }
        // Fecha seleccionada (local)
        const selected = new Date(inputDate.value + 'T00:00:00');
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const diffDays = (selected - today) / (1000 * 60 * 60 * 24);

        if (diffDays < 3) {
            box.classList.remove('hidden');
        } else {
            box.classList.add('hidden');
        }
    };

    inputDate.addEventListener('change', checkLate);
    // Ejecutar al cargar, por si viene de "editar"
    checkLate();
});
</script>
