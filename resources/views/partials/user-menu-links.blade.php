{{-- Пункты меню пользователя в зависимости от роли (шапка сайта, мобильное меню, панели) --}}
@php($user = auth()->user())
<div class="px-3 py-2">
    <div class="fw-bold lh-tight">{{ $user->name }}</div>
    <div class="small text-secondary">{{ $user->email }}</div>
    <span class="badge rounded-pill bg-{{ $user->role->color() }}-subtle text-{{ $user->role->color() }}-emphasis mt-1">{{ $user->role->label() }}</span>
</div>
<div class="dropdown-divider"></div>

@if ($user->isStaff())
    <a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> {{ $user->isAdmin() ? 'Админ-панель' : 'Панель оператора' }}</a>
@elseif ($user->isCourier())
    <a class="dropdown-item" href="{{ route('courier.available') }}"><i class="bi bi-truck"></i> Кабинет курьера</a>
@endif

@if ($user->isProducer())
    <a class="dropdown-item" href="{{ route('producer.dashboard') }}"><i class="bi bi-shop"></i> Кабинет производителя</a>
@endif

@if ($user->canShop())
    <a class="dropdown-item" href="{{ route('account.dashboard') }}"><i class="bi bi-person-circle"></i> Личный кабинет</a>
    <a class="dropdown-item" href="{{ route('account.orders.index') }}"><i class="bi bi-bag-check"></i> Мои заказы</a>
    <a class="dropdown-item" href="{{ route('account.favorites') }}"><i class="bi bi-heart"></i> Избранное</a>
    <a class="dropdown-item" href="{{ route('account.messages') }}"><i class="bi bi-chat-dots"></i> Сообщения
        @if (($nav['messages'] ?? 0) > 0)
            <span class="badge rounded-pill text-bg-danger ms-auto">{{ $nav['messages'] }}</span>
        @endif
    </a>
@endif

@if ($user->role === \App\Enums\UserRole::Buyer)
    <a class="dropdown-item" href="{{ route('producer-application.create') }}"><i class="bi bi-patch-plus"></i> Стать производителем</a>
@endif

<a class="dropdown-item" href="{{ route('notifications.index') }}"><i class="bi bi-bell"></i> Уведомления</a>
<a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-gear"></i> Настройки профиля</a>
<div class="dropdown-divider"></div>
<form method="POST" action="{{ route('logout') }}" data-no-loading>
    @csrf
    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right text-danger"></i> Выйти</button>
</form>
