<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') — @endif{{ $nav['siteName'] }}</title>
    <meta name="description" content="@yield('description', 'Маркетплейс локальных производителей: продукты, выпечка, напитки, цветы и изделия ручной работы с доставкой курьером.')">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="has-tabbar" data-login-url="{{ route('login') }}">
    @include('partials.header')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.mobile-tabbar')
    @include('partials.flash')
    @include('partials.confirm-modal')
    @stack('scripts')
</body>
</html>
