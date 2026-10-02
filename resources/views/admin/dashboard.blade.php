@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="dash-page-head">
        <div>
            <h1>Панель администратора</h1>
            <p>Ключевые показатели платформы на {{ now()->translatedFormat('j F Y') }}.</p>
        </div>
        <a href="{{ route('admin.settings.edit') }}" class="btn btn-light"><i class="bi bi-sliders"></i> Настройки</a>
    </div>

    @if ($summary['pending_applications'])
        <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span><i class="bi bi-person-badge me-1"></i>Заявок производителей на проверке: <strong>{{ $summary['pending_applications'] }}</strong></span>
            <a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-warning">Рассмотреть</a>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-4 col-xxl-2"><x-stat-card icon="people" label="Пользователей" :value="$summary['users']" /></div>
        <div class="col-6 col-xl-4 col-xxl-2"><x-stat-card icon="shop" label="Производителей" :value="$summary['producers']" variant="info" /></div>
        <div class="col-6 col-xl-4 col-xxl-2"><x-stat-card icon="box-seam" label="Товаров" :value="$summary['products']" variant="dark" /></div>
        <div class="col-6 col-xl-4 col-xxl-2"><x-stat-card icon="receipt" label="Заказов" :value="$summary['orders']" :hint="$summary['orders_active'].' в работе'" variant="warning" /></div>
        <div class="col-6 col-xl-4 col-xxl-2"><x-stat-card icon="cash-stack" label="Оборот" :value="money($summary['turnover'])" hint="выполненные заказы" /></div>
        <div class="col-6 col-xl-4 col-xxl-2"><x-stat-card icon="percent" label="Комиссия платформы" :value="money($summary['commission'])" :hint="'≈ '.rtrim(rtrim(number_format(\App\Models\Setting::number('platform_commission_percent'), 2, ',', ''), '0'), ',').'% от оборота товаров'" variant="info" /></div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header">Оборот за 14 дней</div>
                <div class="card-body">
                    <div class="chart-box">
                        <canvas data-chart="bar" data-format="money" data-label="Оборот"
                                data-labels='@json($turnover['labels'])' data-values='@json($turnover['values'])'></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <span>Новые заявки</span>
                    <a href="{{ route('admin.applications.index') }}" class="small fw-semibold">Все</a>
                </div>
                <div class="card-body">
                    @forelse ($applications as $application)
                        <a href="{{ route('admin.applications.show', $application) }}" class="d-flex align-items-center gap-2 text-reset {{ $loop->last ? '' : 'mb-3' }}">
                            <x-producer-logo :producer="$application->producer" size="sm" />
                            <div class="min-w-0 flex-grow-1">
                                <div class="fw-semibold text-truncate">{{ $application->producer->name }}</div>
                                <div class="small text-secondary">{{ $application->user->name }} · {{ $application->created_at->diffForHumans() }}</div>
                            </div>
                            <i class="bi bi-chevron-right text-secondary"></i>
                        </a>
                    @empty
                        <x-empty-state icon="person-check" title="Заявок нет" text="Все заявки рассмотрены." :compact="true" />
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="table-card mt-4">
        <div class="table-card__head">
            <h2>Последние заказы</h2>
            <a href="{{ route('admin.orders.index') }}" class="link-arrow small">Все заказы <i class="bi bi-arrow-right"></i></a>
        </div>
        @include('admin.orders.partials.table', ['orders' => $recentOrders])
    </div>
@endsection
