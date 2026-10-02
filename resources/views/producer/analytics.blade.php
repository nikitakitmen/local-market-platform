@extends('layouts.producer')

@section('title', 'Аналитика')

@section('content')
    <div class="dash-page-head">
        <div>
            <h1>Аналитика</h1>
            <p>Выручка считается по выполненным заказам (стоимость товаров за вычетом скидок).</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><x-stat-card icon="cash-stack" label="Выручка" :value="money($summary['revenue'])" /></div>
        <div class="col-6 col-xl-3"><x-stat-card icon="percent" label="Комиссия платформы" :value="money($summary['commission'])" variant="warning" /></div>
        <div class="col-6 col-xl-3"><x-stat-card icon="wallet2" label="К выплате" :value="money($summary['payout'])" variant="info" /></div>
        <div class="col-6 col-xl-3"><x-stat-card icon="graph-up" label="Средний чек" :value="money($summary['average_check'])" variant="dark" /></div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header">Выручка за 30 дней</div>
                <div class="card-body">
                    <div class="chart-box">
                        <canvas data-chart="line" data-format="money" data-label="Выручка"
                                data-labels='@json($revenue['labels'])' data-values='@json($revenue['values'])'></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">Заказы по статусам</div>
                <div class="card-body">
                    @if (array_sum($statuses['values']) > 0)
                        <div class="chart-box chart-box-sm">
                            <canvas data-chart="doughnut" data-labels='@json($statuses['labels'])' data-values='@json($statuses['values'])'></canvas>
                        </div>
                    @else
                        <x-empty-state icon="pie-chart" title="Нет данных" text="Пока нет заказов." :compact="true" />
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="table-card mt-4">
        <div class="table-card__head"><h2>Самые продаваемые товары</h2></div>
        @if ($topProducts->isEmpty())
            <x-empty-state icon="bar-chart" title="Продаж пока нет" text="Рейтинг появится после первых выполненных заказов." :compact="true" />
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>#</th><th>Товар</th><th>Продано, шт.</th><th>Выручка</th></tr></thead>
                    <tbody>
                        @foreach ($topProducts as $item)
                            <tr>
                                <td class="text-secondary">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $item->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td class="fw-semibold">{{ money($item->revenue) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
