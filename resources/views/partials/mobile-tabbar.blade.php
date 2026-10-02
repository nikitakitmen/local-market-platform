{{-- Нижняя навигация на телефонах --}}
@php($user = auth()->user())
<nav class="mobile-tabbar d-lg-none" aria-label="Мобильная навигация">
    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="bi bi-house"></i>Главная</a>
    <a href="{{ route('catalog') }}" class="{{ request()->routeIs('catalog*') ? 'active' : '' }}"><i class="bi bi-grid"></i>Каталог</a>
    @if (! $user || $user->canShop())
        <a href="{{ route('cart.index') }}" class="{{ request()->routeIs('cart.*', 'checkout.*') ? 'active' : '' }}">
            <i class="bi bi-bag"></i>Корзина
            <span class="counter-badge" data-cart-count data-count="{{ $nav['cart'] }}">{{ $nav['cart'] ?: '' }}</span>
        </a>
        <a href="{{ $user ? route('account.favorites') : route('login') }}" class="{{ request()->routeIs('account.favorites') ? 'active' : '' }}">
            <i class="bi bi-heart"></i>Избранное
        </a>
    @endif
    <a href="{{ $user ? $user->homeUrl() : route('login') }}" class="{{ request()->routeIs('account.*', 'profile.*', 'login') && ! request()->routeIs('account.favorites') ? 'active' : '' }}">
        <i class="bi bi-person"></i>{{ $user ? 'Кабинет' : 'Войти' }}
    </a>
</nav>
