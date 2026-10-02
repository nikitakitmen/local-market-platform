@extends('layouts.producer')

@section('title', 'Главная')

@section('content')
    @include('producer.partials.status-banner')

    <div class="dash-page-head">
        <div>
            <h1>{{ $producer->name }}</h1>
            <p>Сводка по заказам и продажам.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('producers.show', $producer->slug) }}" class="btn btn-light"><i class="bi bi-shop-window"></i> Витрина</a>
            @can('create', \App\Models\Product::class)
                <a href="{{ route('producer.products.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Добавить товар</a>
            @endcan
        </div>
    </div>

    @if ($summary['orders_new'] > 0)
        <div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span><i class="bi bi-bell me-1"></i>Новых заказов, ожидающих подтверждения: <strong>{{ $summary['orders_new'] }}</strong></span>
            <a href="{{ route('producer.orders.index', ['status' => 'new']) }}" class="btn btn-sm btn-primary">Обработать</a>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><x-stat-card icon="receipt" label="Заказов всего" :value="$summary['orders_total']" :hint="$summary['orders_completed'].' выполнено'" /></div>
        <div class="col-6 col-xl-3"><x-stat-card icon="cash-stack" label="Выручка" :value="money($summary['revenue'])" hint="по выполненным заказам" variant="info" /></div>
        <div class="col-6 col-xl-3"><x-stat-card icon="graph-up" label="Средний чек" :value="money($summary['average_check'])" variant="warning" /></div>
        <div class="col-6 col-xl-3"><x-stat-card icon="box-seam" label="Товаров" :value="$summary['products_count']" :hint="$summary['products_active'].' опубликовано'" variant="dark" /></div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Выручка за 14 дней</span>
                    <a href="{{ route('producer.analytics') }}" class="small fw-semibold">Аналитика <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="card-body">
                    <div class="chart-box">
                        <canvas data-chart="bar" data-format="money" data-label="Выручка"
                                data-labels='@json($revenue['labels'])' data-values='@json($revenue['values'])'></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">Популярные товары</div>
                <div class="card-body">
                    @forelse ($topProducts as $item)
                        <div class="d-flex justify-content-between align-items-center gap-2 {{ $loop->last ? '' : 'mb-3' }}">
                            <div class="min-w-0">
                                <div class="fw-semibold text-truncate">{{ $item->product_name }}</div>
                                <div class="small text-secondary">{{ $item->quantity }} шт. · {{ money($item->revenue) }}</div>
                            </div>
                            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis">#{{ $loop->iteration }}</span>
                        </div>
                    @empty
                        <x-empty-state icon="bar-chart" title="Пока нет продаж" text="Здесь появятся товары-лидеры." :compact="true" />
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="table-card mt-4">
        <div class="table-card__head">
            <h2>Последние заказы</h2>
            <a href="{{ route('producer.orders.index') }}" class="link-arrow small">Все заказы <i class="bi bi-arrow-right"></i></a>
        </div>
        @include('producer.orders.partials.table', ['orders' => $recentOrders])
    </div>
@endsection
