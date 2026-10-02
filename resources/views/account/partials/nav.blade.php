{{-- Меню личного кабинета покупателя --}}
@php($user = auth()->user())
<div class="account-nav">
    <div class="account-nav__user">
        <span class="avatar avatar-lg">{{ $user->initials() }}</span>
        <div class="min-w-0">
            <div class="fw-bold text-truncate">{{ $user->name }}</div>
            <div class="small text-secondary text-truncate">{{ $user->email }}</div>
        </div>
    </div>
    <div class="account-nav__links">
        <a href="{{ route('account.dashboard') }}" class="dash-link {{ request()->routeIs('account.dashboard') ? 'active' : '' }}"><i class="bi bi-grid-1x2"></i><span>Обзор</span></a>
        <a href="{{ route('profile.edit') }}" class="dash-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"><i class="bi bi-person"></i><span>Профиль</span></a>
        <a href="{{ route('account.orders.index') }}" class="dash-link {{ request()->routeIs('account.orders.*') ? 'active' : '' }}"><i class="bi bi-bag-check"></i><span>Мои заказы</span></a>
        <a href="{{ route('account.favorites') }}" class="dash-link {{ request()->routeIs('account.favorites') ? 'active' : '' }}"><i class="bi bi-heart"></i><span>Избранное</span></a>
        <a href="{{ route('account.reviews') }}" class="dash-link {{ request()->routeIs('account.reviews') ? 'active' : '' }}"><i class="bi bi-star"></i><span>Мои отзывы</span></a>
        <a href="{{ route('account.addresses.index') }}" class="dash-link {{ request()->routeIs('account.addresses.*') ? 'active' : '' }}"><i class="bi bi-geo-alt"></i><span>Адреса</span></a>
        <a href="{{ route('account.messages') }}" class="dash-link {{ request()->routeIs('account.messages') ? 'active' : '' }}">
            <i class="bi bi-chat-dots"></i><span>Сообщения</span>
            @if ($nav['messages'])
                <span class="badge rounded-pill text-bg-danger">{{ $nav['messages'] }}</span>
            @endif
        </a>
        <a href="{{ route('notifications.index') }}" class="dash-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <i class="bi bi-bell"></i><span>Уведомления</span>
            @if ($nav['notifications'])
                <span class="badge rounded-pill text-bg-danger">{{ $nav['notifications'] }}</span>
            @endif
        </a>
        @if ($user->isProducer())
            <a href="{{ route('producer.dashboard') }}" class="dash-link"><i class="bi bi-shop"></i><span>Кабинет производителя</span></a>
        @else
            <a href="{{ route('producer-application.create') }}" class="dash-link {{ request()->routeIs('producer-application.*') ? 'active' : '' }}"><i class="bi bi-patch-plus"></i><span>Стать производителем</span></a>
        @endif
    </div>
</div>
