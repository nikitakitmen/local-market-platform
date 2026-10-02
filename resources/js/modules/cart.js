/**
 * Корзина: добавление товара без перезагрузки страницы (AJAX),
 * изменение количества и удаление позиций на странице корзины.
 */
import { request } from './http';
import { showToast } from './toast';

/** Обновить счётчики корзины в шапке и мобильном меню с анимацией. */
export function updateCartCount(count) {
    document.querySelectorAll('[data-cart-count]').forEach((badge) => {
        badge.textContent = count > 0 ? count : '';
        badge.dataset.count = count;
        badge.classList.remove('is-bumping');
        void badge.offsetWidth;
        badge.classList.add('is-bumping');
    });
}

function initAddToCart() {
    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('[data-cart-form]')) return;

        event.preventDefault();
        const button = form.querySelector('[type="submit"]');
        const original = button.innerHTML;
        button.classList.add('is-loading');
        button.innerHTML = '<span class="btn-spinner" style="display:inline-block"></span><span>Добавляем…</span>';

        try {
            const data = await request(form.action, { method: 'POST', body: new FormData(form) });
            updateCartCount(data.count);
            showToast(data.message, 'success');

            button.classList.remove('is-loading');
            button.classList.add('is-added');
            button.innerHTML = '<i class="bi bi-check2"></i><span>В корзине</span>';
            setTimeout(() => {
                button.classList.remove('is-added');
                button.innerHTML = original;
            }, 1800);
        } catch (error) {
            button.classList.remove('is-loading');
            button.innerHTML = original;
            if (error.message !== 'unauthenticated') {
                showToast(error.message, 'error');
            }
        }
    });
}

/** На странице корзины форма изменения количества отправляется автоматически. */
function initCartPage() {
    const root = document.getElementById('cartContent');
    if (!root) return;

    let timer = null;

    const send = async (form) => {
        const item = form.closest('.cart-item');
        item?.classList.add('is-updating');

        try {
            const method = (form.querySelector('input[name="_method"]')?.value || 'POST').toUpperCase();
            const body = new FormData(form);
            body.delete('_method');
            const data = await request(form.action, { method, body });
            root.innerHTML = data.html;
            updateCartCount(data.count);
            if (data.message) showToast(data.message, 'success');
        } catch (error) {
            item?.classList.remove('is-updating');
            showToast(error.message, 'error');
        }
    };

    root.addEventListener('submit', (event) => {
        const form = event.target;
        if (!form.matches('[data-cart-ajax]')) return;
        event.preventDefault();
        send(form);
    });

    // Кнопки +/- внутри формы количества
    root.addEventListener('change', (event) => {
        const input = event.target;
        if (!input.matches('[data-qty-input]')) return;
        const form = input.closest('form[data-cart-ajax]');
        if (!form) return;
        clearTimeout(timer);
        timer = setTimeout(() => send(form), 350);
    });
}

/** Универсальный переключатель количества: кнопки − и + рядом с полем. */
function initQtySteppers() {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-qty-step]');
        if (!button) return;

        const stepper = button.closest('.qty-stepper');
        const input = stepper.querySelector('input');
        const min = parseInt(input.min || '1', 10);
        const max = parseInt(input.max || '99', 10);
        const value = Math.min(max, Math.max(min, (parseInt(input.value, 10) || min) + parseInt(button.dataset.qtyStep, 10)));

        if (value !== parseInt(input.value, 10)) {
            input.value = value;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
}

export function initCart() {
    initAddToCart();
    initCartPage();
    initQtySteppers();
}
