@if ($orders->isEmpty())
    <x-empty-state icon="receipt" title="Заказов нет" :compact="true" />
@else
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr><th>Заказ</th><th>Покупатель</th><th>Производитель</th><th>Сумма</th><th>Статус</th><th></th></tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td>
                            <div class="fw-semibold text-nowrap">{{ $order->number }}</div>
                            <div class="small text-secondary">{{ $order->created_at->translatedFormat('j M, H:i') }}</div>
                        </td>
                        <td>{{ $order->buyer->name }}</td>
                        <td>{{ $order->producer->name }}</td>
                        <td class="fw-semibold text-nowrap">{{ money($order->total) }}</td>
                        <td>
                            <div class="d-flex flex-column align-items-start gap-1">
                                <x-status-badge :status="$order->status" />
                                @if ($order->isProblem())
                                    <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis"><i class="bi bi-exclamation-triangle me-1"></i>Проблемный</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-end"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-light btn-sm">Открыть</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
