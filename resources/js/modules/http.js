/**
 * Обёртка над fetch для AJAX-запросов к Laravel:
 * добавляет CSRF-токен, заголовки JSON и показывает полосу загрузки.
 */
import { progressStart, progressDone } from './progress';

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export async function request(url, { method = 'GET', body = null } = {}) {
    const headers = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken(),
    };

    // Для DELETE/PATCH отправляем метод через поле _method (как обычные формы Laravel)
    let payload = body;
    let fetchMethod = method;

    if (!(body instanceof FormData) && body !== null) {
        payload = new FormData();
        Object.entries(body).forEach(([key, value]) => payload.append(key, value));
    }

    if (!['GET', 'POST'].includes(method)) {
        payload = payload ?? new FormData();
        payload.append('_method', method);
        fetchMethod = 'POST';
    }

    progressStart();

    try {
        const response = await fetch(url, { method: fetchMethod, headers, body: payload, credentials: 'same-origin' });
        const data = await response.json().catch(() => ({}));

        // Гость пытается выполнить действие — отправляем на страницу входа
        if (response.status === 401) {
            window.location.href = document.body.dataset.loginUrl || '/login';
            throw new Error('unauthenticated');
        }

        if (!response.ok) {
            const firstError = data.errors ? Object.values(data.errors)[0]?.[0] : null;
            const error = new Error(firstError || data.message || 'Что-то пошло не так. Попробуйте ещё раз.');
            error.status = response.status;
            error.data = data;
            throw error;
        }

        return data;
    } finally {
        progressDone();
    }
}
