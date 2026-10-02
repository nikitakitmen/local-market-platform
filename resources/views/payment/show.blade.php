@extends('layouts.app')

@section('title', 'Оплата заказа')

@section('content')
    <div class="container py-5">
        <div class="pay-demo">
            <div class="text-center mb-4">
                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis mb-2"><i class="bi bi-cone-striped me-1"></i>Демонстрационный режим</span>
                <h1 class="h3 mb-1">Оплата {{ $method === \App\Enums\PaymentMethod::Sbp ? 'по СБП' : 'банковской картой' }}</h1>
                <p class="text-secondary mb-0">
                    {{ $orders->count() > 1 ? 'Заказы' : 'Заказ' }} {{ $orders->map->number->implode(', ') }}
                </p>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-baseline mb-4">
                        <span class="text-secondary">К оплате</span>
                        <span class="fs-3 fw-bold">{{ money($total) }}</span>
                    </div>

                    @if ($method === \App\Enums\PaymentMethod::Sbp)
                        <x-demo-qr :seed="$checkoutId" class="mb-3" />
                        <p class="text-center small text-secondary">Отсканируйте QR-код в приложении банка. В демо-режиме просто нажмите кнопку ниже.</p>
                    @else
                        <div class="card-mock mb-4">
                            <div class="d-flex justify-content-between"><i class="bi bi-credit-card-2-front fs-3"></i><span class="fw-bold">DEMO</span></div>
                            <div class="card-mock__number">4242 4242 4242 4242</div>
                            <div class="d-flex justify-content-between small"><span>CARD HOLDER</span><span>12/30</span></div>
                        </div>
                        {{-- Поля без атрибута name: данные карты никуда не отправляются и не сохраняются --}}
                        <div class="row g-2 mb-2">
                            <div class="col-12"><input type="text" class="form-control" value="4242 4242 4242 4242" disabled aria-label="Номер карты"></div>
                            <div class="col-6"><input type="text" class="form-control" value="12/30" disabled aria-label="Срок действия"></div>
                            <div class="col-6"><input type="text" class="form-control" value="•••" disabled aria-label="CVC"></div>
                        </div>
                        <p class="small text-secondary"><i class="bi bi-shield-lock me-1"></i>Это тестовая форма: данные карты не передаются на сервер и не хранятся.</p>
                    @endif

                    <form method="POST" action="{{ route('payment.pay', $checkoutId) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            {{ $method === \App\Enums\PaymentMethod::Sbp ? 'Я оплатил(а)' : 'Оплатить '.money($total) }}
                        </button>
                    </form>
                    <a href="{{ route('checkout.success', $checkoutId) }}" class="btn btn-link w-100 mt-2 text-secondary">Оплатить позже</a>
                </div>
            </div>
        </div>
    </div>
@endsection
