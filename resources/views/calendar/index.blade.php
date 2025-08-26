{{-- resources/views/calendar/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Calendario Institucional') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($canManage)
                <div class="mb-4">
                    <button id="btnNewEvent"
                        class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
                        + Nuevo evento
                    </button>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    {{-- Modal: Crear/Editar --}}
    @if($canManage)
    <div id="eventModal" class="fixed inset-0 hidden items-center justify-center bg-black/50 z-50" aria-hidden="true">
        <div class="bg-white dark:bg-gray-900 p-5 rounded-lg w-full max-w-lg">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-semibold" id="modalTitle">Nuevo evento</h3>
                <button class="text-gray-500 hover:text-gray-700" data-close="eventModal">&times;</button>
            </div>

            <form id="eventForm" class="space-y-3">
                @csrf
                <input type="hidden" id="event_id">

                <div>
                    <label class="block text-sm mb-1">Título</label>
                    <input id="title" type="text" class="w-full border rounded px-3 py-2" required>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm mb-1">Inicio</label>
                        <input id="start" type="datetime-local" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Fin (opcional)</label>
                        <input id="end" type="datetime-local" class="w-full border rounded px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-sm mb-1">Ubicación (opcional)</label>
                    <input id="location" type="text" class="w-full border rounded px-3 py-2" placeholder="Aula 3, Auditorio, Cancha, etc.">
                </div>

                <div class="flex items-center gap-2">
                    <input id="all_day" type="checkbox">
                    <span>Todo el día</span>
                </div>

                <div>
                    <label class="block text-sm mb-1">Color (opcional)</label>
                    <input id="color" type="text" class="w-full border rounded px-3 py-2" placeholder="#3b82f6">
                </div>

                <div>
                    <label class="block text-sm mb-1">Descripción (opcional)</label>
                    <textarea id="description" class="w-full border rounded px-3 py-2" rows="4"></textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700"
                            data-close="eventModal">Cancelar</button>
                    <button type="submit" class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Modal: Detalle / acciones --}}
    <div id="showModal" class="fixed inset-0 hidden items-center justify-center bg-black/50 z-50" aria-hidden="true">
        <div class="bg-white dark:bg-gray-900 p-5 rounded-lg w-full max-w-lg">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-semibold" id="showTitle">Detalle del evento</h3>
                <button class="text-gray-500 hover:text-gray-700" data-close="showModal">&times;</button>
            </div>

            <div class="space-y-2 text-sm">
                <div><span class="font-medium">Fechas:</span> <span id="showDates"></span></div>
                <div><span class="font-medium">Ubicación:</span> <span id="showLocation"></span></div>
                <div>
                    <span class="font-medium">Descripción:</span>
                    <p id="showDescription" class="mt-1 whitespace-pre-wrap"></p>
                </div>
            </div>

            <div class="mt-4 flex gap-2 justify-end">
                @if($canManage)
                    <button id="btnEdit" class="px-3 py-2 rounded bg-amber-500 text-white hover:bg-amber-600">Editar</button>
                    <button id="btnDelete" class="px-3 py-2 rounded bg-red-600 text-white hover:bg-red-700">Eliminar</button>
                @endif
                <button class="px-3 py-2 rounded bg-gray-200 dark:bg-gray-700" data-close="showModal">Cerrar</button>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/@fullcalendar/core@6.1.15/index.global.min.css">
        <style>
            /* Asegurar visibilidad de modales */
            .hidden {
                display: none !important;
            }
            .flex {
                display: flex !important;
            }
            #showModal, #eventModal {
                z-index: 9999 !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/@fullcalendar/core@6.1.15/index.global.min.js"></script>
        <script src="https://unpkg.com/@fullcalendar/daygrid@6.1.15/index.global.min.js"></script>
        <script src="https://unpkg.com/@fullcalendar/timegrid@6.1.15/index.global.min.js"></script>
        <script src="https://unpkg.com/@fullcalendar/interaction@6.1.15/index.global.min.js"></script>
        <script src="https://unpkg.com/@fullcalendar/core@6.1.15/locales/es.global.min.js"></script>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Depuración inicial
            console.log('FullCalendar cargado:', typeof FullCalendar);
            console.log('showModal:', document.getElementById('showModal'));
            console.log('calendar:', document.getElementById('calendar'));

            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const canManage = @json($canManage);

            // ===== Modales robustos =====
            function openModal(id) {
                const m = document.getElementById(id);
                if (!m) {
                    console.error('openModal: no existe', id);
                    return;
                }
                m.classList.remove('hidden');
                m.classList.add('flex');
                m.setAttribute('aria-hidden', 'false');
                m.style.display = 'flex';
                console.log(`Modal ${id} abierto`);
            }

            function closeModal(id) {
                const m = document.getElementById(id);
                if (!m) {
                    console.error('closeModal: no existe', id);
                    return;
                }
                m.classList.add('hidden');
                m.classList.remove('flex');
                m.setAttribute('aria-hidden', 'true');
                m.style.display = 'none';
                console.log(`Modal ${id} cerrado`);
            }

            // Delegación: botones con data-close
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-close]');
                if (btn) {
                    e.preventDefault();
                    closeModal(btn.getAttribute('data-close'));
                }
                const target = e.target;
                if (target && target.id && target.classList && target.classList.contains('bg-black/50')) {
                    closeModal(target.id);
                }
            });

            // Cerrar con ESC
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    ['eventModal', 'showModal'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el && !el.classList.contains('hidden')) closeModal(id);
                    });
                }
            });

            // ===== Refs formulario =====
            const form = document.getElementById('eventForm');
            const modalTitle = document.getElementById('modalTitle');
            const inputId = document.getElementById('event_id');
            const inputTitle = document.getElementById('title');
            const inputStart = document.getElementById('start');
            const inputEnd = document.getElementById('end');
            const inputAllDay = document.getElementById('all_day');
            const inputColor = document.getElementById('color');
            const inputDesc = document.getElementById('description');
            const inputLocation = document.getElementById('location');

            // ===== Refs detalle =====
            const showTitle = document.getElementById('showTitle');
            const showDates = document.getElementById('showDates');
            const showDesc = document.getElementById('showDescription');
            const showLocation = document.getElementById('showLocation');

            // Botón nuevo
            const btnNew = document.getElementById('btnNewEvent');
            if (btnNew) btnNew.addEventListener('click', () => {
                resetForm();
                modalTitle.textContent = 'Nuevo evento';
                openModal('eventModal');
            });

            function resetForm() {
                inputId.value = '';
                inputTitle.value = '';
                inputStart.value = '';
                inputEnd.value = '';
                inputAllDay.checked = false;
                inputColor.value = '';
                inputDesc.value = '';
                inputLocation.value = '';
            }

            // ===== Calendario =====
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) {
                console.error('Elemento #calendar no encontrado');
                return;
            }

            const calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'es',
                timeZone: 'America/Tijuana',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                initialView: 'dayGridMonth',
                selectable: canManage,
                editable: false,
                navLinks: true,
                events: {
                    url: '{{ route('calendar.fetch') }}',
                    failure: (error) => {
                        console.error('Error al cargar eventos:', error);
                        alert('No se pudieron cargar los eventos.');
                    },
                    success: (events) => {
                        console.log('Eventos cargados:', events);
                    }
                },
                dateClick: function(info) {
                    if (!canManage) return;
                    resetForm();
                    inputStart.value = info.dateStr + 'T08:00';
                    modalTitle.textContent = 'Nuevo evento';
                    openModal('eventModal');
                },
                eventClick: function(info) {
                    console.log('Evento clicado:', info.event);
                    console.log('Propiedades extendidas:', info.event.extendedProps);
                    try {
                        const ev = info.event;
                        const ext = ev.extendedProps || {};

                        // Pintar detalle
                        showTitle.textContent = ev.title || '(sin título)';
                        const startTxt = ev.start ? ev.start.toLocaleString('es-MX', { dateStyle: 'medium', timeStyle: 'short' }) : '';
                        const endTxt = ev.end ? ev.end.toLocaleString('es-MX', { dateStyle: 'medium', timeStyle: 'short' }) : '';
                        showDates.textContent = endTxt ? `${startTxt} — ${endTxt}` : startTxt;
                        showDesc.textContent = (ext.description || '').trim() || '(sin descripción)';
                        showLocation.textContent = (ext.location || '').trim() || '(sin ubicación)';

                        @if($canManage)
                        const btnEdit = document.getElementById('btnEdit');
                        const btnDelete = document.getElementById('btnDelete');

                        if (btnEdit) {
                            btnEdit.onclick = () => {
                                resetForm();
                                modalTitle.textContent = 'Editar evento';
                                inputId.value = ev.id;
                                inputTitle.value = ev.title || '';
                                inputStart.value = ev.start ? toInputDT(ev.start) : '';
                                inputEnd.value = ev.end ? toInputDT(ev.end) : '';
                                inputAllDay.checked = !!ev.allDay;
                                inputColor.value = (ev.backgroundColor || ext.color || '').trim();
                                inputDesc.value = ext.description || '';
                                inputLocation.value = ext.location || '';
                                closeModal('showModal');
                                openModal('eventModal');
                            };
                        }

                        if (btnDelete) {
                            btnDelete.onclick = async () => {
                                if (!confirm('¿Eliminar este evento?')) return;
                                try {
                                    const res = await fetch(`{{ url('/calendar/events') }}/${ev.id}`, {
                                        method: 'DELETE',
                                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                                    });
                                    if (res.ok) {
                                        calendar.refetchEvents();
                                        closeModal('showModal');
                                    } else {
                                        const t = await res.text();
                                        console.error('Error al eliminar:', t);
                                        alert('No se pudo eliminar.\n\n' + t);
                                    }
                                } catch (error) {
                                    console.error('Error en fetch DELETE:', error);
                                    alert('Error al eliminar el evento.');
                                }
                            };
                        }
                        @endif

                        openModal('showModal');
                    } catch (error) {
                        console.error('Error en eventClick:', error);
                        alert('Error al mostrar el evento. Revisa la consola para más detalles.');
                    }
                }
            });

            calendar.render();
            console.log('Calendario renderizado');

            // ===== Guardar (crear/actualizar) =====
            if (form) form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const id = inputId.value.trim();
                const payload = {
                    title: inputTitle.value.trim(),
                    start: inputStart.value ? new Date(inputStart.value).toISOString() : null,
                    end: inputEnd.value ? new Date(inputEnd.value).toISOString() : null,
                    all_day: inputAllDay.checked ? 1 : 0,
                    color: (inputColor.value || '').trim() || null,
                    description: inputDesc.value,
                    location: (inputLocation.value || '').trim() || null,
                };

                if (!payload.title || !payload.start) {
                    alert('Título e inicio son obligatorios.');
                    return;
                }

                const url = id ? `{{ url('/calendar/events') }}/${id}` : `{{ route('calendar.store') }}`;
                const method = id ? 'PUT' : 'POST';

                try {
                    const res = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    if (res.ok) {
                        closeModal('eventModal');
                        calendar.refetchEvents();
                    } else {
                        const t = await res.text();
                        console.error('Error al guardar:', t);
                        alert('No se pudo guardar.\n\n' + t);
                    }
                } catch (error) {
                    console.error('Error en fetch:', error);
                    alert('Error al guardar el evento.');
                }
            });

            // Util: Date -> value datetime-local
            function toInputDT(date) {
                const pad = n => String(n).padStart(2, '0');
                const yyyy = date.getFullYear();
                const mm = pad(date.getMonth() + 1);
                const dd = pad(date.getDate());
                const hh = pad(date.getHours());
                const mi = pad(date.getMinutes());
                return `${yyyy}-${mm}-${dd}T${hh}:${mi}`;
            }
        });
        </script>
    @endpush
</x-app-layout>