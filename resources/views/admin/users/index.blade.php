@extends('layouts.admin')

@php
    $titles = ['courier' => 'Курьеры', 'operator' => 'Операторы'];
    $title = $titles[$role?->value] ?? 'Пользователи';
@endphp

@section('title', $title)

@section('content')
    <div class="dash-page-head">
        <div>
            <h1>{{ $title }}</h1>
            <p>
                @if ($role === \App\Enums\UserRole::Courier)
                    Курьеры платформы. Курьер видит доставки только в своём городе.
                @elseif ($role === \App\Enums\UserRole::Operator)
                    Операторы обрабатывают заказы, доставки и жалобы, но не имеют доступа к настройкам.
                @else
                    Все зарегистрированные пользователи. Заблокированный пользователь не может войти.
                @endif
            </p>
        </div>
        <a href="{{ route('admin.users.create', ['role' => $role?->value ?? 'courier']) }}" class="btn btn-primary"><i class="bi bi-person-plus"></i> Добавить {{ $role === \App\Enums\UserRole::Operator ? 'оператора' : ($role === \App\Enums\UserRole::Courier ? 'курьера' : 'сотрудника') }}</a>
    </div>

    <div class="table-card">
        <div class="table-card__head">
            <form method="GET" class="filter-bar w-100" data-no-loading>
                <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Имя, email или телефон">
                <select name="role" class="form-select" data-autosubmit>
                    <option value="">Все роли</option>
                    @foreach (\App\Enums\UserRole::cases() as $case)
                        <option value="{{ $case->value }}" @selected($role === $case)>{{ $case->label() }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-select" data-autosubmit>
                    <option value="">Любой статус</option>
                    <option value="blocked" @selected(request('status') === 'blocked')>Заблокированные</option>
                </select>
                <button type="submit" class="btn btn-light"><i class="bi bi-search"></i></button>
            </form>
        </div>

        @if ($users->isEmpty())
            <x-empty-state icon="people" title="Пользователи не найдены" :compact="true" />
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Пользователь</th><th>Телефон</th><th>Роль</th><th>Город</th>
                            @if ($role === \App\Enums\UserRole::Courier)<th>Доставки</th>@endif
                            <th>Статус</th><th>Регистрация</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar">{{ $user->initials() }}</span>
                                        <div class="min-w-0">
                                            <div class="fw-semibold">{{ $user->name }}</div>
                                            <div class="small text-secondary">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="small text-nowrap">{{ $user->phone ?: '—' }}</td>
                                <td><span class="badge rounded-pill bg-{{ $user->role->color() }}-subtle text-{{ $user->role->color() }}-emphasis">{{ $user->role->label() }}</span></td>
                                <td class="small">{{ $user->city?->name ?? '—' }}</td>
                                @if ($role === \App\Enums\UserRole::Courier)
                                    <td class="small text-nowrap">{{ $user->active_deliveries }} в работе · {{ $user->completed_deliveries }} выполнено</td>
                                @endif
                                <td>
                                    @if ($user->is_active)
                                        <span class="badge rounded-pill badge-status bg-success-subtle text-success-emphasis">Активен</span>
                                    @else
                                        <span class="badge rounded-pill badge-status bg-danger-subtle text-danger-emphasis">Заблокирован</span>
                                    @endif
                                </td>
                                <td class="small text-secondary text-nowrap">{{ $user->created_at->translatedFormat('j M Y') }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-light btn-icon btn-sm" title="Редактировать"><i class="bi bi-pencil"></i></a>
                                    @unless ($user->is(auth()->user()))
                                        <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="d-inline"
                                              @if ($user->is_active) data-confirm="Заблокировать {{ $user->name }}? Пользователь не сможет войти." data-confirm-button="Заблокировать" @endif>
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-light btn-icon btn-sm {{ $user->is_active ? 'text-danger' : 'text-success' }}" title="{{ $user->is_active ? 'Заблокировать' : 'Разблокировать' }}">
                                                <i class="bi {{ $user->is_active ? 'bi-slash-circle' : 'bi-check-circle' }}"></i>
                                            </button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="table-card__foot">{{ $users->links() }}</div>
            @endif
        @endif
    </div>
@endsection
