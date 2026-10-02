/**
 * Тонкая полоса загрузки вверху страницы (для AJAX и переходов между страницами).
 */
let bar = null;
let active = 0;

function getBar() {
    if (!bar) {
        bar = document.createElement('div');
        bar.className = 'page-progress';
        document.body.appendChild(bar);
    }
    return bar;
}

export function progressStart() {
    active++;
    const el = getBar();
    el.classList.remove('is-done');
    // перезапуск анимации
    void el.offsetWidth;
    el.classList.add('is-active');
}

export function progressDone() {
    active = Math.max(0, active - 1);
    if (active > 0) return;
    const el = getBar();
    el.classList.add('is-done');
    setTimeout(() => el.classList.remove('is-active', 'is-done'), 350);
}

export function initPageProgress() {
    // Показываем полосу при переходе по ссылкам и отправке обычных форм
    window.addEventListener('beforeunload', () => progressStart());
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) progressDone();
    });
}
