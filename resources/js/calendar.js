import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';

if (!window.__calendarInit) {
  window.__calendarInit = true;

  document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const elCalendar = document.getElementById('calendar');
    if (!elCalendar) return;

    const canManage = elCalendar.dataset.canManage === '1';

    // ------- Modales y formularios (IDs que SÍ existen en tu Blade) -------
    const modalCreate = document.getElementById('modal-create');
    const modalShow   = document.getElementById('modal-show');

    const formCreate  = document.getElementById('form-create');
    const formShow    = document.getElementById('form-show');

    // Campos de create
    const cTitle      = formCreate?.elements['title'];
    const cStart      = formCreate?.elements['start'];
    const cEnd        = formCreate?.elements['end'];
    const cAllDay     = formCreate?.elements['all_day'];
    const cLocation   = formCreate?.elements['location'];
    const cNotes      = formCreate?.elements['notes'];
    const cRequestSound      = formCreate?.elements['request_sound'];
    const cSoundRequirements = formCreate?.elements['sound_requirements'];

    // Campos de show/edit
    const sId         = formShow?.elements['id'];
    const sTitle      = formShow?.elements['title'];
    const sStart      = formShow?.elements['start'];
    const sEnd        = formShow?.elements['end'];
    const sAllDay     = formShow?.elements['all_day'];
    const sLocation   = formShow?.elements['location'];
    const sNotes      = formShow?.elements['notes'];

    const btnNewEvent   = document.getElementById('btn-new-event');
    const btnEdit       = document.getElementById('btn-edit');
    const btnDelete     = document.getElementById('btn-delete');
    const btnCancelEdit = document.getElementById('btn-cancel-edit');
    const btnSave       = document.getElementById('btn-save');

    // ------- Helpers UI -------
    const open  = (el) => { if (!el) return; el.classList.remove('hidden'); el.classList.add('flex'); };
    const close = (el) => { if (!el) return; el.classList.add('hidden'); el.classList.remove('flex'); };

    const toLocalInputValue = (value) => {
      if (!value) return '';
      const d = new Date(value);
      d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
      return d.toISOString().slice(0, 16);
    };

    const setDisabledShowForm = (disabled) => {
      [sTitle, sStart, sEnd, sAllDay, sLocation, sNotes].forEach(inp => inp && (inp.disabled = disabled));
      if (btnCancelEdit) btnCancelEdit.style.display = disabled ? 'none' : '';
      if (btnSave)       btnSave.style.display       = disabled ? 'none' : '';
    };

    const resetCreateForm = () => {
      if (!formCreate) return;
      cTitle.value = '';
      cStart.value = toLocalInputValue(new Date());
      cEnd.value   = '';
      cAllDay.checked = false;
      cLocation.value = '';
      cNotes.value    = '';
      if (cRequestSound)      cRequestSound.checked = false;
      if (cSoundRequirements) cSoundRequirements.value = '';
    };

    // ------- Botón "Nuevo evento" -------
    if (btnNewEvent && canManage) {
      btnNewEvent.addEventListener('click', () => {
        resetCreateForm();
        open(modalCreate);
      });
    }

    // ------- FullCalendar -------
    const calendar = new Calendar(elCalendar, {
      plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
      initialView: 'dayGridMonth',
      height: 'auto',
      headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
      events: '/calendar/events',
      editable: false,
      selectable: false,
      eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: false },
      eventClassNames: (arg) => arg.event.extendedProps?.type === 'birthday' ? ['fc-birthday'] : [],

      eventClick: async (info) => {
        const isBirthday = info.event.extendedProps?.type === 'birthday';
        if (isBirthday) {
          const name = info.event.extendedProps?.name || info.event.title.replace(/^🎂\s*Cumpleaños:\s*/i, '');
          alert(`🎉 ¡Felicidades a ${name}!`);
          return;
        }

        const id  = info.event.id;
        const res = await fetch(`/calendar/events/${id}`, { headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
        if (!res.ok) { alert('No se pudo cargar el evento'); return; }
        const payload = await res.json();
        const data = payload.event || payload;

        if (sId)       sId.value       = data.id ?? '';
        if (sTitle)    sTitle.value    = data.title ?? '';
        if (sStart)    sStart.value    = toLocalInputValue(data.start);
        if (sEnd)      sEnd.value      = toLocalInputValue(data.end);
        if (sAllDay)   sAllDay.checked = !!data.all_day;
        if (sLocation) sLocation.value = data.location ?? '';
        if (sNotes)    sNotes.value    = data.notes ?? data.description ?? '';

        setDisabledShowForm(true);
        open(modalShow);
      },
    });

    calendar.render();

    // ------- Crear (POST) -------
    formCreate?.addEventListener('submit', async (e) => {
      e.preventDefault();

      const body = {
        title: cTitle.value,
        start: cStart.value,
        end:   cEnd.value || null,
        all_day: cAllDay.checked ? 1 : 0,
        location: cLocation.value || null,
        notes: cNotes.value || null,
        request_sound: cRequestSound?.checked ? 1 : 0,
        sound_requirements: cSoundRequirements?.value || null,
      };

      const res = await fetch('/calendar/events', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify(body),
      });

      if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        alert('Error al crear: ' + (err.message || 'verifica los datos.'));
        return;
      }

      const data = await res.json().catch(() => ({}));

      if (data.late_warning) {
        alert(data.late_warning);
      }

      close(modalCreate);
      calendar.refetchEvents();
    });

    // ------- Editar (UI) -------
    btnEdit?.addEventListener('click', () => {
      if (!canManage) return;
      setDisabledShowForm(false);
    });

    btnCancelEdit?.addEventListener('click', () => {
      setDisabledShowForm(true);
    });

    // ------- Guardar cambios (PATCH) -------
    formShow?.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!canManage) return;
      const id = sId.value;
      if (!id) return;

      const body = {
        title: sTitle.value,
        start: sStart.value,
        end:   sEnd.value || null,
        all_day: sAllDay.checked ? 1 : 0,
        location: sLocation.value || null,
        notes: sNotes.value || null,
      };

      const res = await fetch(`/calendar/events/${id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify(body),
      });

      if (!res.ok) {
        const err = await res.json().catch(() => ({}));
        alert('Error al actualizar: ' + (err.message || 'verifica los datos.'));
        return;
      }
      setDisabledShowForm(true);
      close(modalShow);
      calendar.refetchEvents();
    });

    // ------- Eliminar (DELETE) -------
    btnDelete?.addEventListener('click', async () => {
      if (!canManage) return;
      const id = sId.value;
      if (!id) return;
      if (!confirm('¿Eliminar este evento?')) return;

      const res = await fetch(`/calendar/events/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
      });

      if (!res.ok) { alert('No se pudo eliminar'); return; }
      close(modalShow);
      calendar.refetchEvents();
    });

    // ------- Cerrar modales por botones con data-close -------
    document.querySelectorAll('[data-close]').forEach(btn => {
      btn.addEventListener('click', () => {
        close(modalCreate);
        close(modalShow);
      });
    });
  });
}
