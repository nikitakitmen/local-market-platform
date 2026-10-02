{{-- Страницы ошибок: отдельная раскладка без запросов к базе данных --}}
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') — @yield('title')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.scss'])
</head>
<body>
    <main class="d-flex align-items-center justify-content-center min-vh-100">
        <div class="error-page fade-in-up" style="max-width: 520px">
            <a href="{{ url('/') }}" class="brand justify-content-center mb-4">
                <span class="brand-mark"><i class="bi bi-basket2-fill"></i></span>
                <span class="brand-text">{{ config('app.name') }}</span>
            </a>
            <div class="error-page__code">@yield('code')</div>
            <h1 class="h3 mt-2">@yield('title')</h1>
            <p class="text-secondary mb-4">@yield('message')</p>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="{{ url('/') }}" class="btn btn-primary"><i class="bi bi-house"></i> На главную</a>
                <a href="{{ url('/catalog') }}" class="btn btn-light"><i class="bi bi-grid"></i> В каталог</a>
            </div>
        </div>
    </main>
</body>
</html>
