{{-- Быстрый вход под демо-аккаунтами (показывается только вне production) --}}
@php
    $demo = [
        ['Администратор', 'admin@localmarket.test', 'shield-lock'],
        ['Оператор', 'operator@localmarket.test', 'headset'],
        ['Покупатель', 'buyer@localmarket.test', 'person'],
        ['Производитель (ферма)', 'farm@localmarket.test', 'shop'],
        ['Производитель (пекарня)', 'bakery@localmarket.test', 'shop'],
        ['Курьер', 'courier@localmarket.test', 'bicycle'],
    ];
@endphp
<div class="demo-accounts mt-4 pt-3 border-top">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="fw-bold"><i class="bi bi-magic me-1 text-primary"></i>Демо-аккаунты</span>
        <span class="text-secondary">пароль: <code>password</code></span>
    </div>
    <div class="row g-2">
        @foreach ($demo as [$label, $email, $icon])
            <div class="col-6">
                <button type="button" class="btn btn-light btn-sm w-100 justify-content-start" data-demo-login="{{ $email }}">
                    <i class="bi bi-{{ $icon }}"></i><span class="text-truncate">{{ $label }}</span>
                </button>
            </div>
        @endforeach
    </div>
</div>
