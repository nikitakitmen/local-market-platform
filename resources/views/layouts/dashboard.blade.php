{{-- Общая раскладка кабинетов с боковым меню (производитель, курьер, администратор, оператор) --}}
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') — @endif{{ $nav['siteName'] }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="dashboard-body" data-login-url="{{ route('login') }}">
<div class="dash-wrapper">
    <aside class="dash-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="dashSidebar" aria-labelledby="dashSidebarLabel">
        <div class="dash-brand justify-content-between">
            <a href="{{ route('home') }}" class="brand" id="dashSidebarLabel">
                <span class="brand-mark"><i class="bi bi-basket2-fill"></i></span>
                <span class="brand-text">{{ $nav['siteName'] }}<small>@yield('panel-name', 'Кабинет')</small></span>
            </a>
            <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#dashSidebar" aria-label="Закрыть"></button>
        </div>
        <div class="offcanvas-body">
            <div class="dash-role">
                <span class="text-secondary">{{ auth()->user()->role->label() }}</span>
                <strong>@yield('panel-owner', auth()->user()->name)</strong>
            </div>
            <nav class="dash-nav">
                @yield('sidebar')
            </nav>
            <div class="dash-sidebar-footer">
                <a href="{{ route('home') }}" class="dash-link"><i class="bi bi-shop"></i><span>На сайт</span></a>
                <form method="POST" action="{{ route('logout') }}" data-no-loading>
                    @csrf
                    <button type="submit" class="dash-link w-100 border-0 bg-transparent text-danger"><i class="bi bi-box-arrow-right text-danger"></i><span>Выйти</span></button>
                </form>
            </div>
        </div>
    </aside>

    <div class="dash-main">
        <header class="dash-topbar">
            <button class="header-icon d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#dashSidebar" aria-label="Меню">
                <i class="bi bi-list"></i>
            </button>
            <div class="dash-topbar__title flex-grow-1">@yield('title')</div>
            @include('partials.notifications-dropdown')
            <div class="dropdown">
                <button class="user-chip dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="avatar">{{ auth()->user()->initials() }}</span>
                    <span class="d-none d-md-inline">{{ \Illuminate\Support\Str::limit(auth()->user()->name, 18) }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-padded">
                    @include('partials.user-menu-links')
                </div>
            </div>
        </header>

        <main class="dash-content">
            @yield('content')
            {{-- Общие страницы (профиль, уведомления, сообщения) используют секцию page --}}
            @yield('page')
        </main>
    </div>
</div>

@include('partials.flash')
@include('partials.confirm-modal')
@stack('scripts')
</body>
</html>
