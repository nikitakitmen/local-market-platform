@extends('layouts.app')

@section('title', 'Регистрация')

@section('content')
    <div class="container">
        <div class="auth-card fade-in-up">
            <div class="auth-card__icon"><i class="bi bi-person-plus"></i></div>
            <h1 class="h3 mb-1">Регистрация</h1>
            <p class="text-secondary mb-4">Создайте аккаунт покупателя. Продавать свои товары можно будет после подачи заявки производителя.</p>

            <form method="POST" action="{{ route('register') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Имя</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                           class="form-control @error('name') is-invalid @enderror" autocomplete="name" required autofocus>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror" autocomplete="email" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Телефон</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+7 (900) 123-45-67"
                           class="form-control @error('phone') is-invalid @enderror" autocomplete="tel" required>
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label for="password" class="form-label">Пароль</label>
                        <div class="password-field">
                            <input type="password" id="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" required>
                            <button type="button" class="password-toggle" data-password-toggle aria-label="Показать пароль"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label for="password_confirmation" class="form-label">Повторите пароль</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-control" autocomplete="new-password" required>
                    </div>
                    @error('password')<div class="col-12 mt-1"><div class="invalid-feedback d-block">{{ $message }}</div></div>@enderror
                    <div class="col-12 mt-1"><div class="form-text">Не менее 8 символов, буквы и цифры.</div></div>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" name="terms" id="terms" value="1" @checked(old('terms'))>
                    <label class="form-check-label small" for="terms">Я согласен с условиями использования платформы и обработкой персональных данных</label>
                    @error('terms')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">Создать аккаунт</button>
            </form>

            <p class="text-center text-secondary mt-4 mb-0">
                Уже есть аккаунт? <a href="{{ route('login') }}" class="fw-semibold">Войти</a>
            </p>
        </div>
    </div>
@endsection
