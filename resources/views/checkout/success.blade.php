@extends('layouts.app')

@section('title', 'Заказ оформлен')

@section('content')
    <div class="container py-5" style="max-width: 820px">
        <div class="text-center mb-4 fade-in-up">
            <div class="empty-state__icon mx-auto" style="width: 5rem; height: 5rem; border-radius: 1.5rem; display: grid; place-items: center; background: var(--lm-primary-soft); color: var(--lm-primary); font-size: 2.3rem">
                <i class="bi bi-bag-check"></i>
            </div>
            <h1 class="h2 mt-3 mb-2">Спасибо за заказ!</h1>
            <p class="text-secondary mb-0">
                @if ($orders->count() > 1)
                    Корзина разделена на {{ $orders->count() }} {{ plural($orders->count(), 'заказ', 'заказа', 'заказов') }} — каждый производитель получил свой.
                @else
                    Производитель получил заказ и скоро его подтвердит.
                @endif
                Статус можно отслеживать в личном кабинете.
            </p>
        </div>

        <div class="d-grid gap-3">
            @foreach ($orders as $order)
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                            <div>
                                <div class="fw-bold">Заказ {{ $order->number }}</div>
                                <div class="small text-secondary">{{ $order->producer->name }}</div>
                            </div>
                            <div class="text-end">
                                <x-status-badge :status="$order->status" />
                                <div class="mt-1"><x-status-badge :status="$order->payment_status" /></div>
                            </div>
                        </div>
                        <div class="small text-secondary mb-2">
                            <i class="bi {{ $order->delivery_method->icon() }} me-1"></i>{{ $order->delivery_method->label() }}: {{ $order->address }}
                        </div>
                        <div class="small">{{ $order->items->map(fn ($item) => $item->full_name.' × '.$item->quantity)->implode(', ') }}</div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="fw-bold fs-5">{{ money($order->total) }}</span>
                            <a href="{{ route('account.orders.show', $order) }}" class="btn btn-light btn-sm">Подробнее</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($orders->contains(fn ($order) => $order->awaitsOnlinePayment()))
            <div class="alert alert-warning mt-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span><i class="bi bi-credit-card me-1"></i>Заказ ещё не оплачен.</span>
                <a href="{{ route('payment.show', $orders->first()->checkout_id) }}" class="btn btn-sm btn-warning">Оплатить</a>
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
            <a href="{{ route('account.orders.index') }}" class="btn btn-primary"><i class="bi bi-receipt"></i> Мои заказы</a>
            <a href="{{ route('catalog') }}" class="btn btn-light">Продолжить покупки</a>
        </div>
    </div>
@endsection
