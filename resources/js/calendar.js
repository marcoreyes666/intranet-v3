import { Calendar } from '@fullcalendar/core';
import dayGrid from '@fullcalendar/daygrid';
import interaction from '@fullcalendar/interaction';
import timeGrid from '@fullcalendar/timegrid';
import list from '@fullcalendar/list';

document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('calendar');
  if (!el) return;

  const canEdit = el.dataset.canEdit === '1';

  const calendar = new Calendar(el, {
    plugins: [dayGrid, interaction, timeGrid, list],
    initialView: 'dayGridMonth',
    headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek' },
    locale: 'es',
    events: '/calendar/events',
    selectable: false,   // <- quitamos crear por selección
    editable: canEdit,   // arrastrar/redimensionar solo roles
    eventClick: (info) => {
      if (!canEdit) return;
      // abre modal en modo edición
      const evt = {
        id: info.event.id,
        title: info.event.title,
        start: info.event.start?.toISOString(),
        end: info.event.end?.toISOString(),
        allDay: !!info.event.allDay,
        extendedProps: info.event.extendedProps || {}
      };
      window.dispatchEvent(new CustomEvent('calendar_open', { detail: { mode: 'edit', event: evt } }));
    },
    eventDrop: async (info) => {
      if (!canEdit) return;
      await fetch(`/calendar/events/${info.event.id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({
          start: info.event.start?.toISOString(),
          end: info.event.end?.toISOString(),
        })
      });
    },
    eventResize: async (info) => {
      if (!canEdit) return;
      await fetch(`/calendar/events/${info.event.id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({
          start: info.event.start?.toISOString(),
          end: info.event.end?.toISOString(),
        })
      });
    },
    eventContent: (arg) => {
      const title = document.createElement('div');
      title.innerText = arg.event.title;
      const location = arg.event.extendedProps?.location;
      const wrap = document.createElement('div');
      wrap.appendChild(title);
      if (location) {
        const loc = document.createElement('div');
        loc.style.fontSize = '0.75rem';
        loc.style.opacity = '0.8';
        loc.innerText = location;
        wrap.appendChild(loc);
      }
      return { domNodes: [wrap] };
    }
  });

  // Abrir modal de creación desde botón
  const btn = document.getElementById('btn-open-create');
  if (btn) {
    btn.addEventListener('click', () => {
      const today = new Date().toISOString().substring(0,10);
      window.dispatchEvent(new CustomEvent('calendar_open', { detail: { mode: 'create', presetDate: today } }));
    });
  }

  // Refetch cuando el modal guarda o elimina
  window.addEventListener('calendar:refetch', () => calendar.refetchEvents());

  calendar.render();
});
