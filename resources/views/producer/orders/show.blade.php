@extends('layouts.producer')

@section('title', 'Заказ '.$order->number)

@section('content')
    <x-breadcrumbs :items="['Заказы' => route('producer.orders.index'), $order->number => null]" class="mb-3" />

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

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Состав заказа</div>
                <div class="card-body">
                    @include('orders.partials.items', ['linkProducts' => true])
                    <div class="key-value small"><span>Комиссия платформы ({{ rtrim(rtrim(number_format($order->commission_percent, 2, ',', ''), '0'), ',') }}%)</span><span>{{ money($order->commission_amount) }}</span></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Покупатель и получение</div>
                <div class="card-body">
                    <div class="key-value"><span>Покупатель</span><span>{{ $order->buyer->name }}</span></div>
                    <div class="key-value"><span>Получатель</span><span>{{ $order->recipient_name }}, <a href="tel:{{ preg_replace('/[^\d+]/', '', $order->recipient_phone) }}">{{ $order->recipient_phone }}</a></span></div>
                    <div class="key-value"><span>Способ получения</span><span><i class="bi {{ $order->delivery_method->icon() }} me-1"></i>{{ $order->delivery_method->label() }}</span></div>
                    <div class="key-value"><span>{{ $order->isDelivery() ? 'Адрес доставки' : 'Пункт самовывоза' }}</span><span>{{ $order->address }}</span></div>
                    @if ($order->delivery?->courier)
                        <div class="key-value"><span>Курьер</span><span>{{ $order->delivery->courier->name }}, {{ $order->delivery->courier->phone }}</span></div>
                    @endif
                    <div class="key-value"><span>Оплата</span><span>{{ $order->payment_method->label() }}</span></div>
                    @if ($order->comment)
                        <div class="key-value"><span>Комментарий</span><span>{{ $order->comment }}</span></div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">Действия</div>
                <div class="card-body d-grid gap-2">
                    @if ($order->canBeAccepted())
                        <form method="POST" action="{{ route('producer.orders.accept', $order) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check2-circle"></i> Принять заказ</button>
                        </form>
                    @endif

                    @if ($order->canBeCompletedByProducer())
                        <form method="POST" action="{{ route('producer.orders.complete', $order) }}" data-confirm="Подтвердите, что покупатель забрал заказ {{ $order->number }}." data-confirm-button="Заказ выдан" data-confirm-class="btn-primary">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-bag-check"></i> Заказ выдан покупателю</button>
                        </form>
                    @elseif ($order->status === \App\Enums\OrderStatus::Accepted && $order->isDelivery())
                        <div class="small text-secondary"><i class="bi bi-truck me-1"></i>Заказ будет выполнен автоматически, когда курьер отметит доставку.</div>
                    @endif

                    @if ($order->canBeCancelled())
                        <button class="btn btn-outline-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#cancelForm"><i class="bi bi-x-circle"></i> Отменить заказ</button>
                        <div class="collapse" id="cancelForm">
                            <form method="POST" action="{{ route('producer.orders.cancel', $order) }}" class="mt-2">
                                @csrf
                                <label class="form-label small" for="reason">Причина отмены</label>
                                <input id="reason" type="text" name="reason" class="form-control @error('reason') is-invalid @enderror" placeholder="Например: товар закончился" required>
                                @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <button type="submit" class="btn btn-danger w-100 mt-2">Подтвердить отмену</button>
                            </form>
                        </div>
                    @endif

                    @if ($order->status->isFinal())
                        <div class="small text-secondary">Заказ {{ mb_strtolower($order->status->label()) }}, действий не требуется.</div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">Статус</div>
                <div class="card-body">
                    @include('orders.partials.timeline')
                </div>
            </div>
        </div>
    </div>
@endsection
