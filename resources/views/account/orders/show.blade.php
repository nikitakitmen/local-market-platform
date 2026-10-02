@extends('layouts.account')

@section('title', 'Заказ '.$order->number)

@section('page')
    <x-breadcrumbs :items="['Мои заказы' => route('account.orders.index'), 'Заказ '.$order->number => null]" class="mb-3" />

    <div class="dash-page-head">
        <div>
            <h1>Заказ {{ $order->number }}</h1>
            <p>от {{ $order->created_at->translatedFormat('j F Y, H:i') }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <x-status-badge :status="$order->status" class="fs-6" />
            <x-status-badge :status="$order->payment_status" class="fs-6" />
        </div>
    </div>

    @if ($order->status === \App\Enums\OrderStatus::Cancelled && $order->isPaid() && $order->payment_method->isOnline())
        <div class="alert alert-info"><i class="bi bi-arrow-counterclockwise me-1"></i>Заказ отменён после оплаты — деньги будут возвращены тем же способом (в демо-режиме возврат не выполняется).</div>
    @endif

    @if ($order->awaitsOnlinePayment())
        <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span><i class="bi bi-credit-card me-1"></i>Заказ ожидает оплаты ({{ $order->payment_method->label() }}).</span>
            <a href="{{ route('payment.show', $order->checkout_id) }}" class="btn btn-warning btn-sm">Оплатить {{ money($order->total) }}</a>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Состав заказа</div>
                <div class="card-body">
                    @include('orders.partials.items')
                </div>
            </div>

            @if ($order->canBeReviewed())
                <div class="card mb-4">
                    <div class="card-header">Оцените товары</div>
                    <div class="card-body d-grid gap-2">
                        @foreach ($order->items->unique('product_id') as $item)
                            @continue(! $item->product || $item->product->trashed())
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <span class="text-truncate">{{ $item->product_name }}</span>
                                @if (in_array($item->product_id, $reviewedProductIds, true))
                                    <span class="small text-secondary text-nowrap"><i class="bi bi-check2-circle text-primary"></i> Отзыв оставлен</span>
                                @else
                                    <a href="{{ route('products.show', $item->product->slug) }}#reviews" class="btn btn-soft btn-sm text-nowrap"><i class="bi bi-star"></i> Оставить отзыв</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">Получение и оплата</div>
                <div class="card-body">
                    <div class="key-value"><span>Способ получения</span><span><i class="bi {{ $order->delivery_method->icon() }} me-1"></i>{{ $order->delivery_method->label() }}</span></div>
                    <div class="key-value"><span>{{ $order->isDelivery() ? 'Адрес доставки' : 'Пункт самовывоза' }}</span><span>{{ $order->address }}</span></div>
                    @if (! $order->isDelivery() && $order->pickupLocation?->working_hours)
                        <div class="key-value"><span>Часы работы</span><span>{{ $order->pickupLocation->working_hours }}</span></div>
                    @endif
                    <div class="key-value"><span>Получатель</span><span>{{ $order->recipient_name }}, {{ $order->recipient_phone }}</span></div>
                    <div class="key-value"><span>Оплата</span><span>{{ $order->payment_method->label() }}</span></div>
                    @if ($order->comment)
                        <div class="key-value"><span>Комментарий</span><span>{{ $order->comment }}</span></div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">Статус</div>
                <div class="card-body">
                    @include('orders.partials.timeline')
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <x-producer-logo :producer="$order->producer" />
                        <div class="min-w-0">
                            <a href="{{ route('producers.show', $order->producer->slug) }}" class="fw-bold text-reset d-block text-truncate">{{ $order->producer->name }}</a>
                            <div class="small text-secondary">{{ $order->producer->phone }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('chat.start', $order->producer) }}">
                        @csrf
                        <button class="btn btn-soft w-100"><i class="bi bi-chat-dots"></i> Написать производителю</button>
                    </form>
                </div>
            </div>

            @if ($order->canBeCancelledByBuyer())
                <form method="POST" action="{{ route('account.orders.cancel', $order) }}"
                      data-confirm="Отменить заказ {{ $order->number }}? Производитель получит уведомление." data-confirm-button="Отменить заказ">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100"><i class="bi bi-x-circle"></i> Отменить заказ</button>
                </form>
            @endif
        </div>
    </div>
@endsection
