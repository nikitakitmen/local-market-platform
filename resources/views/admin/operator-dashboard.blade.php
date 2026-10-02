@extends('layouts.admin')

@section('title', 'Панель оператора')

@section('content')
    <div class="dash-page-head">
        <div>
            <h1>Панель оператора</h1>
            <p>Очередь задач: проблемные заказы, доставки без курьера и жалобы на отзывы.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl"><x-stat-card icon="receipt" label="Новые заказы" :value="$summary['orders_new']" variant="info" /></div>
        <div class="col-6 col-xl"><x-stat-card icon="exclamation-triangle" label="Проблемные заказы" :value="$summary['orders_problem']" variant="danger" /></div>
        <div class="col-6 col-xl"><x-stat-card icon="hourglass-split" label="Ждут курьера" :value="$summary['deliveries_waiting']" variant="warning" /></div>
        <div class="col-6 col-xl"><x-stat-card icon="truck" label="Доставляются" :value="$summary['deliveries_active']" /></div>
        <div class="col-6 col-xl"><x-stat-card icon="flag" label="Жалобы" :value="$summary['reports_pending']" variant="dark" /></div>
    </div>

    <div class="table-card mb-4">
        <div class="table-card__head">
            <h2><i class="bi bi-exclamation-triangle text-danger me-1"></i>Проблемные заказы</h2>
            <a href="{{ route('admin.orders.index', ['problem' => 1]) }}" class="link-arrow small">Все <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="px-3 pt-2 small text-secondary">Новый заказ не принят производителем больше суток или принятый заказ больше 3 часов ждёт курьера.</div>
        @include('admin.orders.partials.table', ['orders' => $problemOrders])
    </div>

    <div class="row g-4">
        <div class="col-xl-6">
            <div class="table-card h-100">
                <div class="table-card__head">
                    <h2>Доставки без курьера</h2>
                    <a href="{{ route('admin.deliveries.index', ['status' => 'waiting']) }}" class="link-arrow small">Все <i class="bi bi-arrow-right"></i></a>
                </div>
                @if ($waitingDeliveries->isEmpty())
                    <x-empty-state icon="check2-circle" title="Все заказы распределены" :compact="true" />
                @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead><tr><th>Заказ</th><th>Производитель</th><th>Принят</th></tr></thead>
                            <tbody>
                                @foreach ($waitingDeliveries as $delivery)
                                    <tr>
                                        <td><a href="{{ route('admin.orders.show', $delivery->order) }}" class="fw-semibold">{{ $delivery->order->number }}</a></td>
                                        <td>{{ $delivery->order->producer->name }}</td>
                                        <td class="small text-secondary">{{ $delivery->order->accepted_at?->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-xl-6">
            <div class="table-card h-100">
                <div class="table-card__head">
                    <h2>Новые жалобы</h2>
                    <a href="{{ route('admin.reports.index') }}" class="link-arrow small">Все <i class="bi bi-arrow-right"></i></a>
                </div>
                @if ($reports->isEmpty())
                    <x-empty-state icon="shield-check" title="Жалоб нет" :compact="true" />
                @else
                    <div class="p-3 d-grid gap-3">
                        @foreach ($reports as $report)
                            <div class="min-w-0">
                                <div class="fw-semibold">{{ $report->reason->label() }} <span class="small text-secondary fw-normal">· {{ $report->user->name }}</span></div>
                                <div class="small text-secondary text-truncate">«{{ $report->review->text }}» — {{ $report->review->product->name }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
