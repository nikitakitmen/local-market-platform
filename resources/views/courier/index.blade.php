@extends('layouts.courier')

@php
    $titles = ['available' => 'Доступные доставки', 'my' => 'Мои доставки', 'completed' => 'Завершённые доставки'];
@endphp

@section('title', $titles[$tab])

@section('content')
    <div class="dash-page-head">
        <div>
            <h1>{{ $titles[$tab] }}</h1>
            <p>
                @if ($tab === 'available')
                    Заказы, принятые производителями{{ auth()->user()->city ? ' в г. '.auth()->user()->city->name : '' }}. Нажмите «Взять заказ», чтобы закрепить его за собой.
                @elseif ($tab === 'my')
                    Заберите заказ у производителя, затем отметьте доставку покупателю.
                @else
                    История выполненных доставок.
                @endif
            </p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><x-stat-card icon="inboxes" label="Доступно" :value="$counts['available']" variant="warning" /></div>
        <div class="col-6 col-xl-3"><x-stat-card icon="truck" label="В работе" :value="$counts['my']" variant="info" /></div>
        <div class="col-6 col-xl-3"><x-stat-card icon="check2-circle" label="Доставлено сегодня" :value="$counts['today']" /></div>
        <div class="col-6 col-xl-3"><x-stat-card icon="trophy" label="Всего доставок" :value="$counts['completed']" variant="dark" /></div>
    </div>

    <div class="status-tabs">
        <a href="{{ route('courier.available') }}" class="{{ $tab === 'available' ? 'active' : '' }}">Доступные <span class="badge rounded-pill text-bg-light">{{ $counts['available'] }}</span></a>
        <a href="{{ route('courier.my') }}" class="{{ $tab === 'my' ? 'active' : '' }}">Мои <span class="badge rounded-pill text-bg-light">{{ $counts['my'] }}</span></a>
        <a href="{{ route('courier.completed') }}" class="{{ $tab === 'completed' ? 'active' : '' }}">Завершённые <span class="badge rounded-pill text-bg-light">{{ $counts['completed'] }}</span></a>
    </div>

    @if ($deliveries->isEmpty())
        <x-empty-state :icon="$tab === 'completed' ? 'check2-all' : 'inbox'"
                       :title="$tab === 'available' ? 'Свободных заказов нет' : ($tab === 'my' ? 'У вас нет активных доставок' : 'Завершённых доставок пока нет')"
                       :text="$tab === 'available' ? 'Новые заказы появятся, когда производители их примут. Обновите страницу чуть позже.' : null">
            @if ($tab === 'my')
                <a href="{{ route('courier.available') }}" class="btn btn-primary">Посмотреть доступные</a>
            @elseif ($tab === 'available')
                <a href="{{ route('courier.available') }}" class="btn btn-light"><i class="bi bi-arrow-clockwise"></i> Обновить</a>
            @endif
        </x-empty-state>
    @else
        <div class="row g-3">
            @foreach ($deliveries as $delivery)
                @php
                    $order = $delivery->order;
                    $pickupPoint = $order->producer->locations->firstWhere('is_pickup_point', true);
                @endphp
                <div class="col-md-6 col-xxl-4">
                    <div class="delivery-card">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="fw-bold">Заказ {{ $order->number }}</div>
                                <div class="small text-secondary">{{ $order->created_at->translatedFormat('j M, H:i') }} · {{ $order->items->sum('quantity') }} {{ plural($order->items->sum('quantity'), 'товар', 'товара', 'товаров') }}</div>
                            </div>
                            <x-status-badge :status="$delivery->status" />
                        </div>

                        <div class="delivery-card__route">
                            <div class="route-point">
                                <small>Забрать у производителя</small>
                                <span class="fw-semibold">{{ $order->producer->name }}</span><br>
                                <span class="text-secondary">{{ $pickupPoint?->address ?? $order->producer->address }}</span>
                                <div><a href="tel:{{ preg_replace('/[^\d+]/', '', $order->producer->phone) }}" class="small">{{ $order->producer->phone }}</a></div>
                            </div>
                            <div class="route-point route-point-end">
                                <small>Доставить покупателю</small>
                                <span class="fw-semibold">{{ $order->recipient_name }}</span><br>
                                <span class="text-secondary">{{ $order->address }}</span>
                                <div><a href="tel:{{ preg_replace('/[^\d+]/', '', $order->recipient_phone) }}" class="small">{{ $order->recipient_phone }}</a></div>
                            </div>
                        </div>

                        @if ($order->comment)
                            <div class="small bg-primary-soft rounded-3 p-2 mb-3"><i class="bi bi-chat-left-text me-1"></i>{{ $order->comment }}</div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center mt-auto mb-3 small">
                            <span class="text-secondary">Сумма заказа</span>
                            <span class="fw-bold fs-6">{{ money($order->total) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3 small">
                            <span class="text-secondary">Оплата</span>
                            <span class="fw-semibold">{{ $order->isPaid() ? 'Оплачен онлайн' : 'Получить '.money($order->total) }}</span>
                        </div>

                        @if ($tab === 'available')
                            <form method="POST" action="{{ route('courier.take', $delivery) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-hand-index-thumb"></i> Взять заказ</button>
                            </form>
                        @elseif ($delivery->status === \App\Enums\DeliveryStatus::Assigned)
                            <form method="POST" action="{{ route('courier.pickup', $delivery) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-box-seam"></i> Забрал у производителя</button>
                            </form>
                        @elseif ($delivery->status === \App\Enums\DeliveryStatus::InTransit)
                            <form method="POST" action="{{ route('courier.deliver', $delivery) }}" data-confirm="Подтвердите, что заказ {{ $order->number }} передан покупателю." data-confirm-button="Доставлено" data-confirm-class="btn-primary">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check2-circle"></i> Доставлено</button>
                            </form>
                        @else
                            <div class="small text-secondary text-center"><i class="bi bi-check2-all text-primary me-1"></i>Доставлено {{ $delivery->delivered_at?->translatedFormat('j F, H:i') }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $deliveries->links() }}</div>
    @endif
@endsection
