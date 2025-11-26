// resources/js/app.js
import './bootstrap';
import './calendar';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// Lucide (iconos del sidebar con data-lucide="...")
// npm i lucide --save  (si aún no está instalada)
import { createIcons, icons } from 'lucide';

// Ejecutar al cargar el DOM (y tras navegaciones inerciales si usas)
document.addEventListener('DOMContentLoaded', () => {
  try { createIcons({ icons }); } catch (e) { /* noop */ }
});

// Si usas Livewire o renders parciales, vuelve a llamar createIcons() tras el update:
// document.addEventListener('livewire:navigated', () => createIcons({ icons }));
