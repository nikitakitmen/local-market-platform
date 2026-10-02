@extends('layouts.app')

@section('title', 'Новый пароль')

@section('content')
    <div class="container">
        <div class="auth-card fade-in-up">
            <div class="auth-card__icon"><i class="bi bi-shield-lock"></i></div>
            <h1 class="h3 mb-4">Новый пароль</h1>

            <form method="POST" action="{{ route('password.store') }}" novalidate>
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $email) }}"
                           class="form-control @error('email') is-invalid @enderror" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Новый пароль</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                        <button type="button" class="password-toggle" data-password-toggle aria-label="Показать пароль"><i class="bi bi-eye"></i></button>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Повторите пароль</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">Сохранить пароль</button>
            </form>
        </div>
    </div>
@endsection
