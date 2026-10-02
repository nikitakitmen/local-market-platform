@extends('layouts.admin')

@section('title', 'Заказ '.$order->number)

@section('content')
    <x-breadcrumbs :items="['Заказы' => route('admin.orders.index'), $order->number => null]" class="mb-3" />

    <div class="dash-page-head">
        <div>
            <h1>Заказ {{ $order->number }}</h1>
            <p>от {{ $order->created_at->translatedFormat('j F Y, H:i') }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <x-status-badge :status="$order->status" class="fs-6" />
            <x-status-badge :status="$order->payment_status" class="fs-6" />
            @if ($order->isProblem())
                <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis fs-6"><i class="bi bi-exclamation-triangle me-1"></i>Проблемный</span>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Состав заказа</div>
                <div class="card-body">
                    @include('orders.partials.items')
                    <div class="key-value small"><span>Комиссия платформы ({{ rtrim(rtrim(number_format($order->commission_percent, 2, ',', ''), '0'), ',') }}%)</span><span>{{ money($order->commission_amount) }}</span></div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">Участники и получение</div>
                <div class="card-body">
                    <div class="key-value"><span>Покупатель</span><span>{{ $order->buyer->name }} · {{ $order->buyer->email }}</span></div>
                    <div class="key-value"><span>Получатель</span><span>{{ $order->recipient_name }}, {{ $order->recipient_phone }}</span></div>
                    <div class="key-value"><span>Производитель</span><span><a href="{{ route('admin.producers.show', $order->producer) }}">{{ $order->producer->name }}</a>, {{ $order->producer->phone }}</span></div>
                    <div class="key-value"><span>Получение</span><span>{{ $order->delivery_method->label() }}: {{ $order->address }}</span></div>
                    @if ($order->delivery)
                        <div class="key-value"><span>Доставка</span><span><x-status-badge :status="$order->delivery->status" /> {{ $order->delivery->courier?->name }}</span></div>
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
                <div class="card-header">Помощь с заказом</div>
                <div class="card-body d-grid gap-2">
                    @if ($order->canBeAccepted())
                        <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="action" value="accept">
                            <button class="btn btn-primary w-100"><i class="bi bi-check2-circle"></i> Принять за производителя</button>
                        </form>
                    @endif
                    @if ($order->status === \App\Enums\OrderStatus::Accepted)
                        <form method="POST" action="{{ route('admin.orders.status', $order) }}" data-confirm="Отметить заказ {{ $order->number }} выполненным?" data-confirm-button="Выполнен" data-confirm-class="btn-primary">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="action" value="complete">
                            <button class="btn btn-outline-secondary w-100"><i class="bi bi-bag-check"></i> Отметить выполненным</button>
                        </form>
                    @endif
                    @if ($order->canBeCancelled())
                        <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="action" value="cancel">
                            <label class="form-label small" for="reason">Отмена заказа</label>
                            <input id="reason" type="text" name="reason" class="form-control mb-2 @error('reason') is-invalid @enderror" placeholder="Причина отмены">
                            @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <button class="btn btn-outline-danger w-100"><i class="bi bi-x-circle"></i> Отменить заказ</button>
                        </form>
                    @endif
                    @if ($order->status->isFinal())
                        <div class="small text-secondary">Заказ {{ mb_strtolower($order->status->label()) }}.</div>
                    @endif
                    @if ($order->delivery)
                        <a href="{{ route('admin.deliveries.index', ['status' => $order->delivery->status->value]) }}" class="btn btn-light w-100"><i class="bi bi-truck"></i> К доставкам</a>
                    @endif
                </div>
            </div>
            <div class="card">
                <div class="card-header">Статус</div>
                <div class="card-body">@include('orders.partials.timeline')</div>
            </div>
        </div>
    </div>
@endsection
