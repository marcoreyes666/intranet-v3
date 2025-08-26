// resources/js/app.js

// ...tu resto de imports/JS de la app (Breeze/Alpine/etc.)...

document.addEventListener('DOMContentLoaded', async () => {
  const el = document.getElementById('calendar');

  // Si el calendario NO marca data-use-vite="1", NO lo inicializamos desde Vite
  if (!el || el.getAttribute('data-use-vite') !== '1') {
    return;
  }

  // Dynamic imports (válidos dentro de funciones con Vite)
  const [{ Calendar }, dayGrid, interaction, timeGrid, list] = await Promise.all([
    import('@fullcalendar/core'),
    import('@fullcalendar/daygrid'),
    import('@fullcalendar/interaction'),
    import('@fullcalendar/timegrid'),
    import('@fullcalendar/list'),
  ]);

  const canEdit = el.dataset.canEdit === '1';

  const calendar = new Calendar(el, {
    plugins: [dayGrid.default, interaction.default, timeGrid.default, list.default],
    initialView: 'dayGridMonth',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
    },
    locale: 'es',
    events: '/calendar/events',
    selectable: false,     // crear por selección desactivado
    editable: canEdit,     // drag/resize solo para roles con permiso
    eventClick: (info) => {
      if (!canEdit) return; // este flujo abre tu modal propio solo si eres editor
      const evt = {
        id: info.event.id,
        title: info.event.title,
        start: info.event.start?.toISOString(),
        end: info.event.end?.toISOString(),
        allDay: !!info.event.allDay,
        extendedProps: info.event.extendedProps || {}
      };
      // Si usas un modal propio fuera de Vite, podrías emitir un evento global:
      window.dispatchEvent(new CustomEvent('calendar_open', {
        detail: { mode: 'edit', event: evt }
      }));
    },
    eventDrop: async (info) => {
      if (!canEdit) return;
      await fetch(`/calendar/events/${info.event.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
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
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token)').content
        },
        body: JSON.stringify({
          start: info.event.start?.toISOString(),
          end: info.event.end?.toISOString(),
        })
      });
    },
    eventContent: (arg) => {
      const wrap = document.createElement('div');
      const title = document.createElement('div');
      title.innerText = arg.event.title;
      wrap.appendChild(title);

      const location = arg.event.extendedProps?.location;
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

  // Botón opcional para abrir crear (si existe en tu HTML)
  const btn = document.getElementById('btn-open-create');
  if (btn) {
    btn.addEventListener('click', () => {
      const today = new Date().toISOString().substring(0,10);
      window.dispatchEvent(new CustomEvent('calendar_open', {
        detail: { mode: 'create', presetDate: today }
      }));
    });
  }

  // Refetch cuando el modal guarda o elimina
  window.addEventListener('calendar:refetch', () => calendar.refetchEvents());

  calendar.render();
});
