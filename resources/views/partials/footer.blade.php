<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <a href="{{ route('home') }}" class="brand mb-3">
                    <span class="brand-mark"><i class="bi bi-basket2-fill"></i></span>
                    <span class="brand-text">{{ $nav['siteName'] }}<small>от местных производителей</small></span>
                </a>
                <p class="mb-0" style="max-width: 340px">
                    Платформа для локальной торговли: фермеры, пекарни, обжарщики кофе, флористы и мастерские продают
                    свои товары напрямую покупателям с доставкой курьером.
                </p>
            </div>
            <div class="col-6 col-lg-2">
                <h6>Покупателям</h6>
                <ul>
                    <li><a href="{{ route('catalog') }}">Каталог товаров</a></li>
                    <li><a href="{{ route('catalog.producers') }}">Производители</a></li>
                    <li><a href="{{ route('cart.index') }}">Корзина</a></li>
                    <li><a href="{{ route('account.orders.index') }}">Мои заказы</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-3">
                <h6>Производителям</h6>
                <ul>
                    <li><a href="{{ route('producer-application.create') }}">Стать производителем</a></li>
                    <li><a href="{{ route('producer.dashboard') }}">Кабинет производителя</a></li>
                    <li><a href="{{ route('courier.available') }}">Кабинет курьера</a></li>
                </ul>
            </div>
            <div class="col-lg-3">
                <h6>Поддержка</h6>
                <ul>
                    <li><i class="bi bi-telephone me-2"></i>{{ $nav['supportPhone'] }}</li>
                    <li><i class="bi bi-envelope me-2"></i><a href="mailto:{{ $nav['supportEmail'] }}">{{ $nav['supportEmail'] }}</a></li>
                    <li><i class="bi bi-clock me-2"></i>Ежедневно с 9:00 до 21:00</li>
                </ul>
            </div>
        </div>
        <div class="site-footer__bottom d-flex flex-column flex-md-row justify-content-between gap-2">
            <span>© {{ date('Y') }} {{ $nav['siteName'] }}. Учебный проект.</span>
            <span>Оплата на сайте работает в демонстрационном режиме.</span>
        </div>
    </div>
</footer>
