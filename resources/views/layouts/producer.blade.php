@extends('layouts.dashboard')

@section('panel-name', 'Кабинет производителя')
@section('panel-owner', auth()->user()->producer?->name ?? auth()->user()->name)

@section('sidebar')
    @php($producer = auth()->user()->producer)
    <a href="{{ route('producer.dashboard') }}" class="dash-link {{ request()->routeIs('producer.dashboard') ? 'active' : '' }}"><i class="bi bi-grid-1x2"></i><span>Главная</span></a>
    <a href="{{ route('producer.products.index') }}" class="dash-link {{ request()->routeIs('producer.products.*') ? 'active' : '' }}"><i class="bi bi-box-seam"></i><span>Товары</span></a>
    <a href="{{ route('producer.orders.index') }}" class="dash-link {{ request()->routeIs('producer.orders.*') ? 'active' : '' }}">
        <i class="bi bi-receipt"></i><span>Заказы</span>
        @if ($newOrders = $producer?->orders()->where('status', \App\Enums\OrderStatus::New)->count())
            <span class="badge rounded-pill text-bg-primary">{{ $newOrders }}</span>
        @endif
    </a>
    <a href="{{ route('producer.locations.index') }}" class="dash-link {{ request()->routeIs('producer.locations.*') ? 'active' : '' }}"><i class="bi bi-geo-alt"></i><span>Торговые точки</span></a>
    <a href="{{ route('producer.reviews.index') }}" class="dash-link {{ request()->routeIs('producer.reviews.*') ? 'active' : '' }}"><i class="bi bi-star"></i><span>Отзывы</span></a>
    <a href="{{ route('producer.messages') }}" class="dash-link {{ request()->routeIs('producer.messages') ? 'active' : '' }}">
        <i class="bi bi-chat-dots"></i><span>Сообщения</span>
        @if ($nav['messages'])
            <span class="badge rounded-pill text-bg-danger">{{ $nav['messages'] }}</span>
        @endif
    </a>
    <a href="{{ route('producer.analytics') }}" class="dash-link {{ request()->routeIs('producer.analytics') ? 'active' : '' }}"><i class="bi bi-graph-up-arrow"></i><span>Аналитика</span></a>
    <a href="{{ route('producer.settings.edit') }}" class="dash-link {{ request()->routeIs('producer.settings.*') ? 'active' : '' }}"><i class="bi bi-gear"></i><span>Настройки</span></a>

    <div class="dash-nav__label">Покупки</div>
    <a href="{{ route('account.orders.index') }}" class="dash-link"><i class="bi bi-bag"></i><span>Мои покупки</span></a>
    @if ($producer)
        <a href="{{ route('producers.show', $producer->slug) }}" class="dash-link"><i class="bi bi-shop-window"></i><span>Моя витрина</span></a>
    @endif
@endsection
