/**
 * Избранное: добавление и удаление товара по клику на «сердечко».
 */
import { request } from './http';
import { showToast } from './toast';

export function initFavorites() {
    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-favorite-toggle]');
        if (!button) return;

        event.preventDefault();
        if (button.disabled) return;
        button.disabled = true;

        try {
            const data = await request(button.dataset.url, { method: 'POST' });

            document.querySelectorAll(`[data-favorite-toggle][data-url="${button.dataset.url}"]`).forEach((btn) => {
                btn.classList.toggle('is-active', data.favorited);
                btn.setAttribute('aria-pressed', data.favorited ? 'true' : 'false');
                btn.setAttribute('title', data.favorited ? 'Убрать из избранного' : 'В избранное');
            });

            button.classList.remove('is-pulsing');
            void button.offsetWidth;
            button.classList.add('is-pulsing');

            document.querySelectorAll('[data-favorites-count]').forEach((badge) => {
                badge.textContent = data.count > 0 ? data.count : '';
                badge.dataset.count = data.count;
            });

            showToast(data.message, 'success');

            // На странице «Избранное» убираем карточку после удаления
            const card = button.closest('[data-favorite-card]');
            if (card && !data.favorited) {
                card.style.transition = 'opacity .25s ease, transform .25s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(.96)';
                setTimeout(() => {
                    card.remove();
                    if (!document.querySelector('[data-favorite-card]')) window.location.reload();
                }, 260);
            }
        } catch (error) {
            if (error.message !== 'unauthenticated') showToast(error.message, 'error');
        } finally {
            button.disabled = false;
        }
    });
}
