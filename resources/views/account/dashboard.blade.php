@extends('layouts.account')

@section('title', 'Личный кабинет')

@section('page')
    <div class="dash-page-head">
        <div>
            <h1>Здравствуйте, {{ $user->name }}!</h1>
            <p>Ваши заказы, избранное и сообщения в одном месте.</p>
        </div>
        <a href="{{ route('catalog') }}" class="btn btn-primary"><i class="bi bi-grid"></i> В каталог</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><x-stat-card icon="bag-check" label="Всего заказов" :value="$stats['orders']" /></div>
        <div class="col-6 col-xl-3"><x-stat-card icon="hourglass-split" label="В работе" :value="$stats['active']" variant="info" /></div>
        <div class="col-6 col-xl-3"><x-stat-card icon="heart" label="В избранном" :value="$stats['favorites']" variant="danger" /></div>
        <div class="col-6 col-xl-3"><x-stat-card icon="star" label="Отзывов" :value="$stats['reviews']" variant="warning" /></div>
    </div>

    @if ($application && ! $user->isProducer())
        <div class="alert alert-{{ $application->status->color() }} d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span>
                <i class="bi bi-person-badge me-1"></i>
                Заявка производителя «{{ $application->producer->name }}»: <strong>{{ mb_strtolower($application->status->label()) }}</strong>
            </span>
            <a href="{{ route('producer-application.create') }}" class="btn btn-sm btn-light">Подробнее</a>
        </div>
    @endif

    <div class="table-card">
        <div class="table-card__head">
            <h2>Последние заказы</h2>
            <a href="{{ route('account.orders.index') }}" class="link-arrow small">Все заказы <i class="bi bi-arrow-right"></i></a>
        </div>
        @if ($recentOrders->isEmpty())
            <x-empty-state icon="bag" title="Заказов пока нет" text="Оформите первый заказ — и он появится здесь." :compact="true">
                <a href="{{ route('catalog') }}" class="btn btn-primary">Выбрать товары</a>
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Заказ</th><th>Продавец</th><th>Сумма</th><th>Статус</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($recentOrders as $order)
                            <tr>
                                <td><div class="fw-semibold text-nowrap">{{ $order->number }}</div><div class="small text-secondary">{{ $order->created_at->translatedFormat('j M Y') }}</div></td>
                                <td>{{ $order->producer->name }}</td>
                                <td class="fw-semibold text-nowrap">{{ money($order->total) }}</td>
                                <td><x-status-badge :status="$order->status" /></td>
                                <td class="text-end"><a href="{{ route('account.orders.show', $order) }}" class="btn btn-light btn-sm">Открыть</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
