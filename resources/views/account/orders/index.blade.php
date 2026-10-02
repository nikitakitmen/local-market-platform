@extends('layouts.account')

@section('title', 'Мои заказы')

@section('page')
    <div class="dash-page-head">
        <div>
            <h1>Мои заказы</h1>
            <p>Заказы разных производителей оформляются отдельно.</p>
        </div>
    </div>

    <div class="status-tabs">
        <a href="{{ route('account.orders.index') }}" class="{{ $status ? '' : 'active' }}">Все <span class="badge rounded-pill text-bg-light">{{ $counts->sum() }}</span></a>
        @foreach (\App\Enums\OrderStatus::cases() as $case)
            <a href="{{ route('account.orders.index', ['status' => $case->value]) }}" class="{{ $status === $case ? 'active' : '' }}">
                {{ $case->label() }} <span class="badge rounded-pill text-bg-light">{{ $counts[$case->value] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    @if ($orders->isEmpty())
        <x-empty-state icon="receipt" title="Заказов нет" text="{{ $status ? 'Нет заказов с таким статусом.' : 'Вы ещё ничего не заказывали.' }}">
            <a href="{{ route('catalog') }}" class="btn btn-primary">Перейти в каталог</a>
        </x-empty-state>
    @else
        <div class="d-grid gap-3">
            @foreach ($orders as $order)
                <a href="{{ route('account.orders.show', $order) }}" class="card card-hover text-reset">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                            <div>
                                <div class="fw-bold">Заказ {{ $order->number }}</div>
                                <div class="small text-secondary">{{ $order->created_at->translatedFormat('j F Y, H:i') }} · {{ $order->producer->name }}</div>
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                <x-status-badge :status="$order->status" />
                                @if ($order->delivery && $order->status === \App\Enums\OrderStatus::Accepted)
                                    <x-status-badge :status="$order->delivery->status" />
                                @endif
                                @if ($order->awaitsOnlinePayment())
                                    <x-status-badge :status="$order->payment_status" />
                                @endif
                            </div>
                        </div>
                        <div class="small text-secondary mt-2 text-truncate">
                            {{ $order->items->map(fn ($item) => $item->full_name.' × '.$item->quantity)->implode(', ') }}
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="small text-secondary"><i class="bi {{ $order->delivery_method->icon() }} me-1"></i>{{ $order->delivery_method->label() }}</span>
                            <span class="fw-bold fs-5">{{ money($order->total) }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
@endsection
