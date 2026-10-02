{{-- История статусов заказа --}}
@php
    $delivery = $order->delivery;
    $steps = [
        ['Заказ оформлен', $order->created_at, true],
        ['Принят производителем', $order->accepted_at, (bool) $order->accepted_at],
    ];

    if ($order->isDelivery()) {
        $steps[] = ['Курьер назначен'.($delivery?->courier ? ': '.$delivery->courier->name : ''), $delivery?->assigned_at, (bool) $delivery?->assigned_at];
        $steps[] = ['Курьер в пути', $delivery?->picked_up_at, (bool) $delivery?->picked_up_at];
        $steps[] = ['Доставлено', $delivery?->delivered_at, (bool) $delivery?->delivered_at];
    } else {
        $steps[] = ['Выдан покупателю', $order->completed_at, (bool) $order->completed_at];
    }
@endphp

<ul class="order-timeline">
    @if ($order->status === \App\Enums\OrderStatus::Cancelled)
        <li class="done">Заказ оформлен <small>{{ $order->created_at->translatedFormat('j F, H:i') }}</small></li>
        <li class="cancelled">
            Заказ отменён
            <small>{{ $order->cancelled_at?->translatedFormat('j F, H:i') }}{{ $order->cancel_reason ? ' · '.$order->cancel_reason : '' }}</small>
        </li>
    @else
        @foreach ($steps as [$label, $time, $done])
            <li class="{{ $done ? 'done' : '' }}">
                {{ $label }}
                <small>{{ $time ? $time->translatedFormat('j F, H:i') : 'ожидается' }}</small>
            </li>
        @endforeach
    @endif
</ul>
