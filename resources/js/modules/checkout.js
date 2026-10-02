/**
 * Оформление заказа: переключение «доставка / самовывоз», выбор адреса
 * и предварительный расчёт стоимости доставки (окончательный расчёт — на сервере).
 */
function formatMoney(value) {
    const rounded = Math.round(value * 100) / 100;
    const hasCents = Math.abs(rounded % 1) > 0.001;
    return rounded.toLocaleString('ru-RU', {
        minimumFractionDigits: hasCents ? 2 : 0,
        maximumFractionDigits: 2,
    }) + ' ₽';
}

export function initCheckout() {
    const form = document.getElementById('checkoutForm');
    if (!form) return;

    const base = parseFloat(form.dataset.deliveryBase);
    const perKm = parseFloat(form.dataset.deliveryPerKm);
    const freeFrom = parseFloat(form.dataset.deliveryFreeFrom);
    const goodsTotal = parseFloat(form.dataset.goodsTotal);

    const deliveryBlock = form.querySelector('[data-delivery-block]');
    const pickupBlocks = form.querySelectorAll('[data-pickup-block]');
    const newAddressBlock = form.querySelector('[data-new-address]');

    const currentMethod = () => form.querySelector('input[name="delivery_method"]:checked')?.value;

    /** Город и удалённость выбранного адреса. */
    const currentAddress = () => {
        const selected = form.querySelector('input[name="address_id"]:checked');
        if (!selected) return null;
        if (selected.value === 'new') {
            const city = form.querySelector('[name="new_address[city_id]"]')?.value;
            const distance = form.querySelector('[name="new_address[distance_km]"]')?.value;
            return { city, distance: parseInt(distance || '0', 10) };
        }
        return { city: selected.dataset.city, distance: parseInt(selected.dataset.distance, 10) };
    };

    const recalc = () => {
        const method = currentMethod();
        const isDelivery = method === 'delivery';
        const address = currentAddress();

        deliveryBlock?.classList.toggle('d-none', !isDelivery);
        pickupBlocks.forEach((block) => block.classList.toggle('d-none', isDelivery));

        const isNew = form.querySelector('input[name="address_id"]:checked')?.value === 'new';
        newAddressBlock?.classList.toggle('d-none', !(isDelivery && isNew));

        let deliveryTotal = 0;
        let unavailable = false;

        form.querySelectorAll('[data-order-group]').forEach((group) => {
            const subtotal = parseFloat(group.dataset.subtotal);
            const costEl = group.querySelector('[data-delivery-cost]');
            const warnEl = group.querySelector('[data-delivery-warning]');
            let text = 'Бесплатно';
            let warn = false;

            if (!isDelivery) {
                text = 'Самовывоз';
            } else if (!address || !address.city) {
                text = '—';
            } else if (address.city !== group.dataset.city) {
                text = 'Недоступна';
                warn = true;
                unavailable = true;
            } else {
                const cost = subtotal >= freeFrom ? 0 : base + perKm * (address.distance || 0);
                deliveryTotal += cost;
                text = cost > 0 ? formatMoney(cost) : 'Бесплатно';
            }

            if (costEl) costEl.textContent = text;
            warnEl?.classList.toggle('d-none', !warn);
        });

        const deliveryEl = form.querySelector('[data-summary-delivery]');
        const totalEl = form.querySelector('[data-summary-total]');
        if (deliveryEl) deliveryEl.textContent = isDelivery ? (deliveryTotal > 0 ? formatMoney(deliveryTotal) : 'Бесплатно') : '0 ₽';
        if (totalEl) totalEl.textContent = formatMoney(goodsTotal + (isDelivery ? deliveryTotal : 0));

        form.querySelector('[data-city-warning]')?.classList.toggle('d-none', !(isDelivery && unavailable));
    };

    form.addEventListener('change', recalc);
    recalc();
}
