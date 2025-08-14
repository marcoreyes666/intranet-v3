<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Calendario institucional') }}
            </h2>

            @php
                $canEdit = auth()->user()->hasRole(['Administrador','Encargado de departamento','Rector']);
            @endphp

            @if($canEdit)
                <button id="btn-open-create"
                        class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    Nuevo evento
                </button>
            @endif
        </div>
    </x-slot>

    <div id="calendar-page"
         x-data="calendarState({{ $canEdit ? 'true' : 'false' }})"
         @calendar_open.window="openModal($event.detail)"
         class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                <div id="calendar" data-can-edit="{{ $canEdit ? '1' : '0' }}"></div>
            </div>
        </div>

        <!-- Modal Crear/Editar -->
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold mb-4" x-text="isEditing ? 'Editar evento' : 'Nuevo evento'"></h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Título *</label>
                        <input x-model="form.title" class="w-full border rounded" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Lugar</label>
                        <input x-model="form.location" class="w-full border rounded" placeholder="Domo, Auditorio, etc.">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Fecha *</label>
                        <input type="date" x-model="form.date" class="w-full border rounded" required>
                    </div>

                    <div>
                        <label class="inline-flex items-center mt-6">
                            <input type="checkbox" x-model="form.all_day" class="rounded">
                            <span class="ml-2 text-sm">Todo el día</span>
                        </label>
                    </div>

                    <template x-if="!form.all_day">
                        <div class="md:col-span-2 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Hora inicio</label>
                                <input type="time" x-model="form.startTime" class="w-full border rounded">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Hora fin</label>
                                <input type="time" x-model="form.endTime" class="w-full border rounded">
                            </div>
                        </div>
                    </template>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Descripción</label>
                        <textarea x-model="form.description" rows="3" class="w-full border rounded"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-between gap-2">
                    <button @click="closeModal" type="button" class="px-4 py-2 border rounded">Cerrar</button>

                    <div class="flex gap-2">
                        <button x-show="isEditing" @click="deleteEvent" type="button"
                                class="px-4 py-2 border border-red-600 text-red-600 rounded hover:bg-red-50">
                            Eliminar
                        </button>
                        <button @click="saveEvent" type="button"
                                class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Estado Alpine --}}
    <script>
    function calendarState(canEdit) {
        return {
            open: false,
            isEditing: false,
            canEdit: !!canEdit,
            editingId: null,
            form: { title:'', location:'', description:'', date:'', startTime:'', endTime:'', all_day:true },

            openModal({ mode = 'create', presetDate = null, event = null } = {}) {
                if (!this.canEdit && mode !== 'view') return;
                this.isEditing = (mode === 'edit');
                this.editingId = event?.id || null;
                this.open = true;

                if (this.isEditing && event) {
                    // precarga para editar (simple)
                    const start = event.start ? event.start.substring(0,10) : '';
                    const st = event.start ? new Date(event.start) : null;
                    const et = event.end ? new Date(event.end) : null;

                    this.form = {
                        title: event.title || '',
                        location: event.extendedProps?.location || '',
                        description: event.extendedProps?.description || '',
                        date: start,
                        all_day: !!event.allDay,
                        startTime: (event.allDay || !st) ? '' : st.toISOString().substring(11,16),
                        endTime: (event.allDay || !et) ? '' : et.toISOString().substring(11,16),
                    };
                } else {
                    this.form = {
                        title:'', location:'', description:'',
                        date: presetDate || new Date().toISOString().substring(0,10),
                        startTime:'', endTime:'', all_day:true
                    };
                }
            },
            closeModal() { this.open = false; this.isEditing = false; this.editingId = null; },

            async saveEvent() {
                const f = this.form;
                if (!f.title || !f.date) { alert('Título y fecha son obligatorios'); return; }

                let startISO, endISO;
                if (f.all_day) {
                    const s = new Date(f.date + 'T00:00:00');
                    const e = new Date(f.date + 'T00:00:00'); e.setDate(e.getDate()+1);
                    startISO = s.toISOString();
                    endISO   = e.toISOString();
                } else {
                    if (!f.startTime) { alert('Indica hora de inicio'); return; }
                    const st = new Date(`${f.date}T${f.startTime}:00`);
                    const et = f.endTime ? new Date(`${f.date}T${f.endTime}:00`) : new Date(st.getTime() + 60*60*1000);
                    if (et < st) { alert('Hora fin debe ser >= inicio'); return; }
                    startISO = st.toISOString();
                    endISO   = et.toISOString();
                }

                const payload = {
                    title: f.title,
                    description: f.description || null,
                    location: f.location || null,
                    all_day: f.all_day,
                    start: startISO,
                    end: endISO,
                    color: null
                };

                const url = this.isEditing ? `/calendar/events/${this.editingId}` : '/calendar/events';
                const method = this.isEditing ? 'PUT' : 'POST';

                await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });

                this.closeModal();
                window.dispatchEvent(new Event('calendar:refetch'));
            },

            async deleteEvent() {
                if (!this.isEditing || !this.editingId) return;
                if (!confirm('¿Eliminar este evento?')) return;
                await fetch(`/calendar/events/${this.editingId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                this.closeModal();
                window.dispatchEvent(new Event('calendar:refetch'));
            }
        }
    }
    </script>
</x-app-layout>
