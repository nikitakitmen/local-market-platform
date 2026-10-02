@extends('layouts.admin')

@section('title', $user->exists ? 'Редактирование пользователя' : 'Новый сотрудник')

@section('content')
    <x-breadcrumbs :items="['Пользователи' => route('admin.users.index'), ($user->exists ? $user->name : 'Новый сотрудник') => null]" class="mb-3" />
    <div class="dash-page-head">
        <h1>{{ $user->exists ? 'Редактирование пользователя' : 'Новый сотрудник' }}</h1>
    </div>

    <div class="card" style="max-width: 760px">
        <div class="card-body">
            <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" novalidate>
                @csrf
                @if ($user->exists)
                    @method('PUT')
                @endif
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Имя</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Телефон</label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="city_id">Город</label>
                        <select id="city_id" name="city_id" class="form-select @error('city_id') is-invalid @enderror">
                            <option value="">Не указан</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" @selected(old('city_id', $user->city_id) == $city->id)>{{ $city->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Для курьера — город, в котором он принимает доставки.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="role">Роль</label>
                        @if ($user->isProducer())
                            <input type="text" class="form-control" value="{{ $user->role->label() }}" disabled>
                            <div class="form-text">Роль производителя управляется через раздел «Производители».</div>
                        @else
                            <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" @disabled($user->is(auth()->user()))>
                                @foreach (\App\Enums\UserRole::assignable() as $case)
                                    <option value="{{ $case->value }}" @selected(old('role', $user->role?->value) === $case->value)>{{ $case->label() }}</option>
                                @endforeach
                            </select>
                            @if ($user->is(auth()->user()))
                                <input type="hidden" name="role" value="{{ $user->role->value }}">
                                <div class="form-text">Нельзя изменить собственную роль.</div>
                            @endif
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password">{{ $user->exists ? 'Новый пароль' : 'Пароль' }}</label>
                        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" placeholder="{{ $user->exists ? 'Оставьте пустым, чтобы не менять' : '' }}">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $user->is_active)) @disabled($user->is(auth()->user()))>
                            <label class="form-check-label" for="is_active">Аккаунт активен</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Сохранить</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light">Отмена</a>
                </div>
            </form>
        </div>
    </div>
@endsection
