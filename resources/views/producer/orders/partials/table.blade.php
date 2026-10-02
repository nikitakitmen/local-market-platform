@if ($orders->isEmpty())
    <x-empty-state icon="receipt" title="Заказов нет" text="Когда покупатели оформят заказы, они появятся здесь." :compact="true" />
@else
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr><th>Заказ</th><th>Покупатель</th><th>Получение</th><th>Сумма</th><th>Статус</th><th></th></tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td>
                            <div class="fw-semibold text-nowrap">{{ $order->number }}</div>
                            <div class="small text-secondary">{{ $order->created_at->translatedFormat('j M, H:i') }}</div>
                        </td>
                        <td>{{ $order->buyer->name }}</td>
                        <td class="small text-nowrap"><i class="bi {{ $order->delivery_method->icon() }} me-1"></i>{{ $order->delivery_method->label() }}</td>
                        <td class="fw-semibold text-nowrap">{{ money($order->total) }}</td>
                        <td>
                            <div class="d-flex flex-column align-items-start gap-1">
                                <x-status-badge :status="$order->status" />
                                @if ($order->delivery && $order->status === \App\Enums\OrderStatus::Accepted)
                                    <x-status-badge :status="$order->delivery->status" />
                                @endif
                            </div>
                        </td>
                        <td class="text-end"><a href="{{ route('producer.orders.show', $order) }}" class="btn btn-light btn-sm">Открыть</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
