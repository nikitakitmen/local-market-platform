/**
 * Главный JS-файл сайта. Подключает Bootstrap и небольшие модули
 * с интерактивностью (без React/Vue — обычный JavaScript).
 */
import * as bootstrap from 'bootstrap';
import { initPageProgress } from './modules/progress';
import { initServerToasts, showToast } from './modules/toast';
import { initForms } from './modules/forms';
import { initCart } from './modules/cart';
import { initFavorites } from './modules/favorites';
import { initProduct } from './modules/product';
import { initCheckout } from './modules/checkout';
import { initChat } from './modules/chat';
import { initVariantsEditor } from './modules/variants-editor';

window.bootstrap = bootstrap;
window.showToast = showToast;

document.addEventListener('DOMContentLoaded', () => {
    initPageProgress();
    initServerToasts();
    initForms();
    initCart();
    initFavorites();
    initProduct();
    initCheckout();
    initChat();
    initVariantsEditor();

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));

    // Chart.js подгружается отдельным файлом только там, где есть графики
    if (document.querySelector('canvas[data-chart]')) {
        import('./modules/charts').then(({ initCharts }) => initCharts());
    }
});
