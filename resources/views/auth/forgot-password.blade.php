@extends('layouts.app')

@section('title', 'Восстановление пароля')

@section('content')
    <div class="container">
        <div class="auth-card fade-in-up">
            <div class="auth-card__icon"><i class="bi bi-key"></i></div>
            <h1 class="h3 mb-1">Восстановление пароля</h1>
            <p class="text-secondary mb-4">Укажите email, и мы отправим ссылку для создания нового пароля.</p>

            @if (session('success'))
                <div class="alert alert-success small">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" novalidate>
                @csrf
                <div class="mb-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror" required autofocus>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">Отправить ссылку</button>
            </form>

            @unless (app()->isProduction())
                <p class="small text-secondary mt-3 mb-0"><i class="bi bi-info-circle me-1"></i>В режиме разработки письмо записывается в <code>storage/logs/laravel.log</code>.</p>
            @endunless

            <p class="text-center mt-4 mb-0"><a href="{{ route('login') }}"><i class="bi bi-arrow-left"></i> Вернуться ко входу</a></p>
        </div>
    </div>
@endsection
