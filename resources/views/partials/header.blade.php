@php($user = auth()->user())
<div class="topbar d-none d-lg-block">
    <div class="container d-flex justify-content-between align-items-center">
        <span><i class="bi bi-geo-alt me-1"></i> Товары малых производителей вашего города — доставка курьером и самовывоз</span>
        <div class="d-flex gap-4">
            <a href="{{ route('catalog.producers') }}">Производители</a>
            @if (! $user || $user->role === \App\Enums\UserRole::Buyer)
                <a href="{{ route('producer-application.create') }}">Стать производителем</a>
            @endif
            <span><i class="bi bi-telephone me-1"></i>{{ $nav['supportPhone'] }}</span>
        </div>
    </div>
</div>

<header class="site-header sticky-top">
    <nav class="navbar" aria-label="Основная навигация">
        <div class="container flex-wrap flex-lg-nowrap">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand-mark"><i class="bi bi-basket2-fill"></i></span>
                <span class="brand-text">{{ $nav['siteName'] }}<small class="d-none d-sm-block">от местных производителей</small></span>
            </a>

            <div class="dropdown d-none d-lg-block">
                <button class="btn btn-primary btn-catalog" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-grid-3x3-gap-fill"></i> Каталог
                </button>
                <div class="dropdown-menu catalog-menu">
                    <div class="row">
                        @foreach ($nav['categories'] as $category)
                            <div class="col-6 col-xl-4 catalog-menu__group">
                                <a class="catalog-menu__title" href="{{ route('catalog', ['category' => $category->slug]) }}">
                                    <i class="bi bi-{{ $category->icon ?: 'tag' }}"></i>{{ $category->name }}
                                </a>
                                @foreach ($category->children as $child)
                                    <a class="catalog-menu__link" href="{{ route('catalog', ['category' => $category->slug, 'subcategory' => $child->slug]) }}">{{ $child->name }}</a>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    <div class="border-top pt-3 d-flex justify-content-between">
                        <a href="{{ route('catalog') }}" class="link-arrow">Все товары <i class="bi bi-arrow-right"></i></a>
                        <a href="{{ route('catalog.producers') }}" class="link-arrow">Все производители <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <form class="header-search d-none d-lg-block" action="{{ route('catalog') }}" method="GET" role="search" data-no-loading>
                <i class="bi bi-search"></i>
                <input class="form-control" type="search" name="q" value="{{ request()->routeIs('catalog') ? request('q') : '' }}" placeholder="Сыр, хлеб, мёд, керамика…" aria-label="Поиск товаров">
                <button class="btn btn-primary" type="submit">Найти</button>
            </form>

            <div class="header-actions ms-auto">
                @if (! $user || $user->canShop())
                    <a href="{{ $user ? route('account.favorites') : route('login') }}" class="header-icon d-none d-sm-inline-grid" title="Избранное">
                        <i class="bi bi-heart"></i>
                        <span class="counter-badge" data-favorites-count data-count="{{ $nav['favorites'] }}">{{ $nav['favorites'] ?: '' }}</span>
                    </a>
                    <a href="{{ route('cart.index') }}" class="header-icon" title="Корзина">
                        <i class="bi bi-bag"></i>
                        <span class="counter-badge" data-cart-count data-count="{{ $nav['cart'] }}">{{ $nav['cart'] ?: '' }}</span>
                    </a>
                @endif

                @auth
                    @if ($user->canShop() && $nav['messages'])
                        <a href="{{ route('account.messages') }}" class="header-icon d-none d-md-inline-grid" title="Сообщения">
                            <i class="bi bi-chat-dots"></i>
                            <span class="counter-badge counter-badge-danger">{{ $nav['messages'] }}</span>
                        </a>
                    @endif
                    @include('partials.notifications-dropdown')
                    <div class="dropdown d-none d-lg-block">
                        <button class="user-chip dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar">{{ $user->initials() }}</span>
                            <span class="d-none d-xl-inline">{{ \Illuminate\Support\Str::limit($user->name, 16) }}</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-padded">
                            @include('partials.user-menu-links')
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-light d-none d-lg-inline-flex ms-1"><i class="bi bi-person"></i> Войти</a>
                @endauth

                <button class="header-icon d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-label="Меню">
                    <i class="bi bi-list"></i>
                </button>
            </div>

            {{-- На главной странице большой поиск уже есть в hero-блоке --}}
            @unless (request()->routeIs('home'))
                <form class="header-search d-lg-none w-100" action="{{ route('catalog') }}" method="GET" role="search" data-no-loading>
                    <i class="bi bi-search"></i>
                    <input class="form-control" type="search" name="q" value="{{ request()->routeIs('catalog') ? request('q') : '' }}" placeholder="Найти товары или производителей" aria-label="Поиск товаров">
                    <button class="btn btn-primary" type="submit">Найти</button>
                </form>
            @endunless
        </div>
    </nav>
</header>

{{-- Мобильное меню --}}
<div class="offcanvas offcanvas-end mobile-menu" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="mobileMenuLabel">Меню</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Закрыть"></button>
    </div>
    <div class="offcanvas-body">
        @auth
            <div class="d-flex align-items-center gap-3 mb-3 p-2 rounded-3 bg-primary-soft">
                <span class="avatar avatar-lg">{{ $user->initials() }}</span>
                <div class="min-w-0">
                    <div class="fw-bold text-truncate">{{ $user->name }}</div>
                    <div class="small text-secondary">{{ $user->role->label() }}</div>
                </div>
            </div>
            <a class="nav-link" href="{{ $user->homeUrl() }}"><i class="bi bi-speedometer2"></i> Мой кабинет</a>
            @if ($user->canShop())
                <a class="nav-link" href="{{ route('account.orders.index') }}"><i class="bi bi-bag-check"></i> Мои заказы</a>
                <a class="nav-link" href="{{ route('account.messages') }}"><i class="bi bi-chat-dots"></i> Сообщения
                    @if ($nav['messages'])<span class="badge rounded-pill text-bg-danger ms-auto">{{ $nav['messages'] }}</span>@endif
                </a>
            @endif
            <a class="nav-link" href="{{ route('notifications.index') }}"><i class="bi bi-bell"></i> Уведомления
                @if ($nav['notifications'])<span class="badge rounded-pill text-bg-danger ms-auto">{{ $nav['notifications'] }}</span>@endif
            </a>
            <a class="nav-link" href="{{ route('profile.edit') }}"><i class="bi bi-gear"></i> Профиль</a>
        @else
            <div class="d-grid gap-2 mb-3">
                <a href="{{ route('login') }}" class="btn btn-primary">Войти</a>
                <a href="{{ route('register') }}" class="btn btn-light">Зарегистрироваться</a>
            </div>
        @endauth

        <div class="small fw-bold text-uppercase text-muted-2 mt-3 mb-1 px-2">Каталог</div>
        @foreach ($nav['categories'] as $category)
            <a class="nav-link" href="{{ route('catalog', ['category' => $category->slug]) }}"><i class="bi bi-{{ $category->icon ?: 'tag' }}"></i> {{ $category->name }}</a>
        @endforeach
        <a class="nav-link" href="{{ route('catalog.producers') }}"><i class="bi bi-shop"></i> Производители</a>

        @if (! $user || $user->role === \App\Enums\UserRole::Buyer)
            <a class="nav-link" href="{{ route('producer-application.create') }}"><i class="bi bi-patch-plus"></i> Стать производителем</a>
        @endif

        @auth
            <form method="POST" action="{{ route('logout') }}" class="mt-3" data-no-loading>
                @csrf
                <button class="btn btn-outline-secondary w-100"><i class="bi bi-box-arrow-right"></i> Выйти</button>
            </form>
        @endauth
    </div>
</div>
