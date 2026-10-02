@extends('layouts.app')

@section('title', 'Вход')

@section('content')
    <div class="container">
        <div class="auth-card fade-in-up">
            <div class="auth-card__icon"><i class="bi bi-box-arrow-in-right"></i></div>
            <h1 class="h3 mb-1">Вход в аккаунт</h1>
            <p class="text-secondary mb-4">Войдите, чтобы оформлять заказы и следить за доставкой.</p>

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror" autocomplete="email" required autofocus>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <label for="password" class="form-label">Пароль</label>
                        <a href="{{ route('password.request') }}" class="small">Забыли пароль?</a>
                    </div>
                    <div class="password-field">
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" data-password-toggle aria-label="Показать пароль"><i class="bi bi-eye"></i></button>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" @checked(old('remember'))>
                    <label class="form-check-label" for="remember">Запомнить меня</label>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">Войти</button>
            </form>

            <p class="text-center text-secondary mt-4 mb-0">
                Нет аккаунта? <a href="{{ route('register') }}" class="fw-semibold">Зарегистрироваться</a>
            </p>

            @unless (app()->isProduction())
                @include('auth.partials.demo-accounts')
            @endunless
        </div>
    </div>
@endsection
