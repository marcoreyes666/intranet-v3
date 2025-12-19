import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY ?? 'local',
  wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
  wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
  wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
  forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
  enabledTransports: ['ws', 'wss'],
  authEndpoint: '/broadcasting/auth',
  auth: {
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
    },
  },
});


function setBadge(count) {
  const badge = document.getElementById('notif-badge');
  if (!badge) return;

  badge.textContent = String(count || 0);
  if (count && count > 0) badge.classList.remove('hidden');
  else badge.classList.add('hidden');
}

function renderNotifList(items) {
  const list = document.getElementById('notif-list');
  if (!list) return;

  if (!items || items.length === 0) {
    list.innerHTML = `<li class="text-sm text-slate-500 dark:text-gray-300 p-2">Sin nuevas notificaciones</li>`;
    return;
  }

  list.innerHTML = items.map((n) => `
    <li>
      <a href="${n.url}" class="block p-2 rounded hover:bg-slate-100 dark:hover:bg-gray-700">
        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">${escapeHtml(n.title)}</div>
        <div class="text-xs text-slate-600 dark:text-gray-300">${escapeHtml(n.body || '')}</div>
      </a>
    </li>
  `).join('');
}

function escapeHtml(str) {
  return String(str ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

// === 1) Inicial: cargar summary (opcional pero recomendable) ===
async function loadNotifSummary() {
  try {
    const res = await window.axios.get('/notifications/summary');
    setBadge(res.data.unreadCount);
    renderNotifList(res.data.latest);
  } catch (_) {}
}

// === 2) Realtime: private user channel ===
function initUserNotifications() {
  if (!window.AUTH_USER_ID) return;

  window.Echo.private(`users.${window.AUTH_USER_ID}`)
    .listen('.notification.pushed', (e) => {
      setBadge(e.unreadCount);
      renderNotifList(e.latest);
    });
}

// === 3) Realtime: tickets channel (mínimo: disparar refresh si estás en tickets) ===
function initTicketsChannel() {
  window.Echo.channel('tickets')
    .listen('.ticket.updated', (e) => {
      // Estrategia mínima: si estás en /tickets, recarga.
      // Después optimizamos para refrescar solo tabla/contador sin reload total.
      if (window.location.pathname.startsWith('/tickets')) {
        window.location.reload();
      }
    });
}

// Boot
document.addEventListener('DOMContentLoaded', () => {
  loadNotifSummary();
  initUserNotifications();
  initTicketsChannel();
});