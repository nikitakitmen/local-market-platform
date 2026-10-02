{{-- Панель управления. Оператор видит только разрешённые ему разделы. --}}
@extends('layouts.dashboard')

@section('panel-name', auth()->user()->isAdmin() ? 'Администрирование' : 'Панель оператора')

@section('sidebar')
    @php($isAdmin = auth()->user()->isAdmin())
    <a href="{{ route('admin.dashboard') }}" class="dash-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>

    @if ($isAdmin)
        <div class="dash-nav__label">Пользователи</div>
        <a href="{{ route('admin.users.index') }}" class="dash-link {{ request()->routeIs('admin.users.*') && ! in_array(request('role'), ['courier', 'operator']) ? 'active' : '' }}"><i class="bi bi-people"></i><span>Пользователи</span></a>
    @endif

    <div class="dash-nav__label">Производители</div>
    <a href="{{ route('admin.producers.index') }}" class="dash-link {{ request()->routeIs('admin.producers.*') ? 'active' : '' }}"><i class="bi bi-shop"></i><span>Производители</span></a>
    @if ($isAdmin)
        <a href="{{ route('admin.applications.index') }}" class="dash-link {{ request()->routeIs('admin.applications.*') ? 'active' : '' }}">
            <i class="bi bi-person-badge"></i><span>Заявки производителей</span>
            @if ($pending = \App\Models\ProducerApplication::where('status', \App\Enums\ProducerStatus::Pending)->count())
                <span class="badge rounded-pill text-bg-warning">{{ $pending }}</span>
            @endif
        </a>
        <a href="{{ route('admin.products.index') }}" class="dash-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"><i class="bi bi-box-seam"></i><span>Товары</span></a>
        <a href="{{ route('admin.categories.index') }}" class="dash-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"><i class="bi bi-tags"></i><span>Категории</span></a>
    @endif

    <div class="dash-nav__label">Заказы и доставка</div>
    <a href="{{ route('admin.orders.index') }}" class="dash-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"><i class="bi bi-receipt"></i><span>Заказы</span></a>
    <a href="{{ route('admin.deliveries.index') }}" class="dash-link {{ request()->routeIs('admin.deliveries.*') ? 'active' : '' }}"><i class="bi bi-truck"></i><span>Доставки</span></a>
    @if ($isAdmin)
        <a href="{{ route('admin.users.index', ['role' => 'courier']) }}" class="dash-link {{ request()->routeIs('admin.users.*') && request('role') === 'courier' ? 'active' : '' }}"><i class="bi bi-bicycle"></i><span>Курьеры</span></a>
        <a href="{{ route('admin.users.index', ['role' => 'operator']) }}" class="dash-link {{ request()->routeIs('admin.users.*') && request('role') === 'operator' ? 'active' : '' }}"><i class="bi bi-headset"></i><span>Операторы</span></a>
    @endif

    <div class="dash-nav__label">Модерация</div>
    @if ($isAdmin)
        <a href="{{ route('admin.reviews.index') }}" class="dash-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}"><i class="bi bi-chat-square-text"></i><span>Отзывы</span></a>
    @endif
    <a href="{{ route('admin.reports.index') }}" class="dash-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
        <i class="bi bi-flag"></i><span>Жалобы</span>
        @if ($reports = \App\Models\ReviewReport::where('status', \App\Enums\ReportStatus::Pending)->count())
            <span class="badge rounded-pill text-bg-danger">{{ $reports }}</span>
        @endif
    </a>

    @if ($isAdmin)
        <div class="dash-nav__label">Платформа</div>
        <a href="{{ route('admin.promo-codes.index') }}" class="dash-link {{ request()->routeIs('admin.promo-codes.*') ? 'active' : '' }}"><i class="bi bi-ticket-perforated"></i><span>Промокоды</span></a>
        <a href="{{ route('admin.settings.edit') }}" class="dash-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"><i class="bi bi-sliders"></i><span>Настройки</span></a>
    @endif
@endsection
