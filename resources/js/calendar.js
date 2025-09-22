// resources/js/calendar.js
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

    // Flag informativo (la visibilidad del botón la controla Blade)
    const canManage = elCalendar.dataset.canManage === '1';

    // Modales y campos
    const modalForm    = document.getElementById('eventFormModal');
    const modalView    = document.getElementById('eventViewModal');

    const eventId      = document.getElementById('eventId');
    const title        = document.getElementById('title');
    const description  = document.getElementById('description');
    const start        = document.getElementById('start');
    const end          = document.getElementById('end');
    const all_day      = document.getElementById('all_day');
    const locationInput= document.getElementById('location');

    const eventDetails = document.getElementById('eventDetails');
    const btnCancelForm= document.getElementById('btnCancelForm');
    const btnSaveForm  = document.getElementById('btnSaveForm');
    const btnCloseView = document.getElementById('btnCloseView');
    const btnEdit      = document.getElementById('btnEdit');
    const btnDelete    = document.getElementById('btnDelete');
    const btnNewEvent  = document.getElementById('btnNewEvent');

    // Helpers UI
    const open  = (el) => { el?.classList.remove('hidden'); el?.classList.add('flex'); };
    const close = (el) => { el?.classList.add('hidden'); el?.classList.remove('flex'); };
    const resetForm = () => {
      if (!eventId) return;
      eventId.value = '';
      title.value = '';
      description.value = '';
      start.value = '';
      end.value = '';
      all_day.checked = false;
      locationInput.value = '';
    };
    const setLocalDatetime = (input, dateObj) => {
      const d = new Date(dateObj);
      d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
      input.value = d.toISOString().slice(0, 16);
    };
    const fmt = (d) => d ? new Date(d).toLocaleString() : '-';
    const escapeHtml = (s) => (s || '').replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
    const nl2br = (s) => (s || '').replace(/\n/g, '<br>');

    // Botón externo "Nuevo evento"
    btnNewEvent?.addEventListener('click', () => {
      resetForm();
      setLocalDatetime(start, new Date());
      document.getElementById('eventFormTitle').textContent = 'Nuevo evento';
      open(modalForm);
    });

    // Calendario
    const calendar = new Calendar(elCalendar, {
      plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
      initialView: 'dayGridMonth',
      height: 'auto',
      headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
      events: '/calendar/events',
      selectable: false,
      editable: false,
      eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: false },

      // Clase especial para cumpleaños
      eventClassNames: (arg) =>
        arg.event.extendedProps?.type === 'birthday' ? ['fc-birthday'] : [],

      // ÚNICO eventClick
      eventClick: async (info) => {
        const isBirthday = info.event.extendedProps?.type === 'birthday';

        // Cumpleaños: solo mensaje, SIN modal ni botones
        if (isBirthday) {
          const name = info.event.extendedProps?.name
            || info.event.title.replace(/^🎂 Cumple:\s*/, '');
          alert(`🎉 ¡Felicidades a ${name}!`);
          return;
        }

        // Evento normal: detalles en modal (con botones si Blade los renderizó)
        if (btnEdit)   btnEdit.style.display = '';
        if (btnDelete) btnDelete.style.display = '';

        const id = info.event.id;
        const res = await fetch(`/calendar/events/${id}`, { headers: { 'X-CSRF-TOKEN': csrf } });
        if (!res.ok) { alert('No se pudo cargar el evento'); return; }
        const data = await res.json();

        eventDetails.innerHTML = `
          <div><strong>Título:</strong> ${escapeHtml(info.event.title)}</div>
          <div><strong>Inicio:</strong> ${fmt(info.event.start)}</div>
          <div><strong>Fin:</strong> ${info.event.end ? fmt(info.event.end) : '-'}</div>
          <div><strong>Todo el día:</strong> ${info.event.allDay ? 'Sí' : 'No'}</div>
          <div><strong>Lugar:</strong> ${escapeHtml(data.location || '-')}</div>
          <div><strong>Descripción:</strong><br>${nl2br(escapeHtml(data.description || '-'))}</div>
        `;
        eventDetails.dataset.id = id;
        open(modalView);
      },
    });

    calendar.render();

    // Crear / Editar (anti-doble submit)
    let isSubmitting = false;
    document.getElementById('eventForm')?.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (isSubmitting) return;
      isSubmitting = true;
      if (btnSaveForm) btnSaveForm.disabled = true;

      try {
        const id = eventId.value;
        const payload = {
          title: title.value,
          description: description.value,
          start: start.value,
          end: end.value || null,
          all_day: all_day.checked ? 1 : 0,
          location: locationInput.value
        };

        const res = await fetch(id ? `/calendar/events/${id}` : '/calendar/events', {
          method: id ? 'PATCH' : 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
          body: JSON.stringify(payload)
        });

        if (!res.ok) {
          const err = await res.json().catch(() => ({}));
          alert('Error: ' + (err.message || 'Revisa los datos.'));
          return;
        }

        close(modalForm);
        calendar.refetchEvents();
      } finally {
        isSubmitting = false;
        if (btnSaveForm) btnSaveForm.disabled = false;
      }
    });

    // Editar desde modal de detalles
    btnEdit?.addEventListener('click', async () => {
      const id = eventDetails.dataset.id;
      const res = await fetch(`/calendar/events/${id}`, { headers: { 'X-CSRF-TOKEN': csrf } });
      if (!res.ok) { alert('No se pudo cargar el evento'); return; }
      const data = await res.json();

      eventId.value = id;
      title.value = data.title || '';
      description.value = data.description || '';
      start.value = data.start || '';
      end.value = data.end || '';
      all_day.checked = !!data.all_day;
      locationInput.value = data.location || '';

      close(modalView);
      document.getElementById('eventFormTitle').textContent = 'Editar evento';
      open(modalForm);
    });

    // Eliminar
    btnDelete?.addEventListener('click', async () => {
      if (!confirm('¿Eliminar este evento?')) return;
      const id = eventDetails.dataset.id;
      const res = await fetch(`/calendar/events/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
      });
      if (!res.ok) { alert('No se pudo eliminar'); return; }
      close(modalView);
      calendar.refetchEvents();
    });

    // Cerrar modales
    btnCancelForm?.addEventListener('click', () => close(modalForm));
    btnCloseView?.addEventListener('click', () => close(modalView));
  });
}
