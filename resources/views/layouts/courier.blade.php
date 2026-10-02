@extends('layouts.dashboard')

@section('panel-name', 'Кабинет курьера')

@section('sidebar')
    <a href="{{ route('courier.available') }}" class="dash-link {{ request()->routeIs('courier.available') ? 'active' : '' }}"><i class="bi bi-inboxes"></i><span>Доступные доставки</span></a>
    <a href="{{ route('courier.my') }}" class="dash-link {{ request()->routeIs('courier.my') ? 'active' : '' }}"><i class="bi bi-truck"></i><span>Мои доставки</span></a>
    <a href="{{ route('courier.completed') }}" class="dash-link {{ request()->routeIs('courier.completed') ? 'active' : '' }}"><i class="bi bi-check2-circle"></i><span>Завершённые</span></a>
    <div class="dash-nav__label">Аккаунт</div>
    <a href="{{ route('notifications.index') }}" class="dash-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}"><i class="bi bi-bell"></i><span>Уведомления</span></a>
    <a href="{{ route('profile.edit') }}" class="dash-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"><i class="bi bi-person"></i><span>Профиль</span></a>
@endsection
