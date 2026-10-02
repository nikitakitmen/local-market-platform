/**
 * Всплывающие уведомления (Bootstrap Toast).
 * showToast('Товар добавлен в корзину') или showToast('Ошибка', 'error').
 */
import { Toast } from 'bootstrap';

const ICONS = {
    success: 'bi-check-circle-fill',
    error: 'bi-exclamation-triangle-fill',
    info: 'bi-info-circle-fill',
};

function container() {
    return document.getElementById('toastContainer');
}

export function showToast(message, type = 'success') {
    const holder = container();
    if (!holder) return;

    const el = document.createElement('div');
    el.className = `toast toast-${type} align-items-center`;
    el.setAttribute('role', 'alert');
    el.setAttribute('aria-live', 'assertive');
    el.setAttribute('aria-atomic', 'true');

    const body = document.createElement('div');
    body.className = 'toast-body';
    body.innerHTML = `<span class="toast-icon"><i class="bi ${ICONS[type] ?? ICONS.info}"></i></span>`;

    const text = document.createElement('div');
    text.className = 'flex-grow-1 pt-1';
    text.textContent = message; // textContent — защита от XSS
    body.appendChild(text);

    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'btn-close ms-2 mt-1';
    close.setAttribute('data-bs-dismiss', 'toast');
    close.setAttribute('aria-label', 'Закрыть');
    body.appendChild(close);

    el.appendChild(body);
    holder.appendChild(el);

    const toast = new Toast(el, { delay: type === 'error' ? 6000 : 3500 });
    el.addEventListener('hidden.bs.toast', () => el.remove());
    toast.show();
}

/** Показываем флеш-сообщения, отрендеренные сервером. */
export function initServerToasts() {
    document.querySelectorAll('[data-flash-toast]').forEach((el) => {
        showToast(el.dataset.message, el.dataset.type || 'success');
        el.remove();
    });
}
