@extends('layouts.admin')

@section('title', 'Доставки')

@section('content')
    <div class="dash-page-head">
        <div>
            <h1>Доставки</h1>
            <p>Курьеры берут заказы сами; при необходимости назначьте курьера вручную или снимите его.</p>
        </div>
    </div>

    <div class="status-tabs">
        <a href="{{ route('admin.deliveries.index') }}" class="{{ $status ? '' : 'active' }}">Все <span class="badge rounded-pill text-bg-light">{{ $counts->sum() }}</span></a>
        @foreach (\App\Enums\DeliveryStatus::cases() as $case)
            <a href="{{ route('admin.deliveries.index', ['status' => $case->value]) }}" class="{{ $status === $case ? 'active' : '' }}">{{ $case->label() }} <span class="badge rounded-pill text-bg-light">{{ $counts[$case->value] ?? 0 }}</span></a>
        @endforeach
    </div>

    <div class="table-card">
        @if ($deliveries->isEmpty())
            <x-empty-state icon="truck" title="Доставок нет" :compact="true" />
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Заказ</th><th>Маршрут</th><th>Курьер</th><th>Статус</th><th>Управление</th></tr></thead>
                    <tbody>
                        @foreach ($deliveries as $delivery)
                            @php($order = $delivery->order)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold">{{ $order->number }}</a>
                                    <div class="small text-secondary">{{ money($order->total) }} · <x-status-badge :status="$order->status" /></div>
                                </td>
                                <td class="small" style="min-width: 240px">
                                    <div><i class="bi bi-shop text-secondary me-1"></i>{{ $order->producer->name }} <span class="text-secondary">({{ $order->producer->city->name }})</span></div>
                                    <div><i class="bi bi-geo-alt text-secondary me-1"></i>{{ $order->address }}</div>
                                </td>
                                <td class="small">{{ $delivery->courier?->name ?? '—' }}</td>
                                <td><x-status-badge :status="$delivery->status" /></td>
                                <td style="min-width: 220px">
                                    @if ($delivery->status === \App\Enums\DeliveryStatus::Waiting && $order->status === \App\Enums\OrderStatus::Accepted)
                                        <form method="POST" action="{{ route('admin.deliveries.assign', $delivery) }}" class="d-flex gap-1">
                                            @csrf
                                            @method('PATCH')
                                            <select name="courier_id" class="form-select form-select-sm" aria-label="Курьер" required>
                                                <option value="">Курьер…</option>
                                                @foreach ($couriers as $courier)
                                                    <option value="{{ $courier->id }}">{{ $courier->name }}{{ $courier->city ? ' ('.$courier->city->name.')' : '' }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-sm btn-primary" type="submit">OK</button>
                                        </form>
                                    @elseif ($delivery->status === \App\Enums\DeliveryStatus::Assigned)
                                        <form method="POST" action="{{ route('admin.deliveries.unassign', $delivery) }}" data-confirm="Снять курьера {{ $delivery->courier?->name }} с заказа {{ $order->number }}?" data-confirm-button="Снять">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="bi bi-person-dash"></i> Снять курьера</button>
                                        </form>
                                    @else
                                        <span class="small text-secondary">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($deliveries->hasPages())
                <div class="table-card__foot">{{ $deliveries->links() }}</div>
            @endif
        @endif
    </div>
@endsection
