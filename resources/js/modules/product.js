/**
 * Страница товара: галерея фотографий и выбор варианта с обновлением цены.
 * Карточки каталога: выбор варианта в выпадающем списке тоже меняет цену.
 */
function initGallery() {
    document.querySelectorAll('[data-gallery]').forEach((gallery) => {
        const main = gallery.querySelector('[data-gallery-main]');
        gallery.querySelectorAll('[data-gallery-thumb]').forEach((thumb) => {
            thumb.addEventListener('click', () => {
                gallery.querySelectorAll('[data-gallery-thumb]').forEach((t) => t.classList.remove('active'));
                thumb.classList.add('active');
                main.classList.add('is-switching');
                setTimeout(() => {
                    main.src = thumb.dataset.src;
                    main.classList.remove('is-switching');
                }, 150);
            });
        });
    });
}

function initVariantPrice() {
    document.addEventListener('change', (event) => {
        const input = event.target;
        if (!input.matches('[data-variant-input]')) return;

        const scope = input.closest('[data-variant-scope]');
        const priceEl = scope?.querySelector('[data-variant-price]');
        const option = input.tagName === 'SELECT' ? input.selectedOptions[0] : input;
        if (priceEl && option?.dataset.price) {
            priceEl.textContent = option.dataset.price;
        }
    });
}

export function initProduct() {
    initGallery();
    initVariantPrice();
}
