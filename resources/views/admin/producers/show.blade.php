@extends('layouts.admin')

@section('title', $producer->name)

@section('content')
    <x-breadcrumbs :items="['Производители' => route('admin.producers.index'), $producer->name => null]" class="mb-3" />

    <div class="dash-page-head">
        <div class="d-flex align-items-center gap-3">
            <x-producer-logo :producer="$producer" />
            <div>
                <h1>{{ $producer->name }}</h1>
                <p>{{ $producer->type->label() }} · г. {{ $producer->city->name }}{{ $producer->inn ? ' · ИНН '.$producer->inn : '' }}</p>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <x-status-badge :status="$producer->status" class="fs-6" />
            <a href="{{ route('producers.show', $producer->slug) }}" class="btn btn-light" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Витрина</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl"><x-stat-card icon="box-seam" label="Товаров" :value="$stats['products']" /></div>
        <div class="col-6 col-xl"><x-stat-card icon="receipt" label="Заказов" :value="$stats['orders']" :hint="$stats['completed'].' выполнено'" variant="info" /></div>
        <div class="col-6 col-xl"><x-stat-card icon="cash-stack" label="Оборот" :value="money($stats['turnover'])" variant="dark" /></div>
        <div class="col-6 col-xl"><x-stat-card icon="percent" label="Комиссия" :value="money($stats['commission'])" variant="warning" /></div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Профиль</div>
                <div class="card-body">
                    <div class="key-value"><span>Владелец</span><span>{{ $producer->user->name }} · {{ $producer->user->email }}</span></div>
                    <div class="key-value"><span>Телефон</span><span>{{ $producer->phone }}</span></div>
                    <div class="key-value"><span>Email магазина</span><span>{{ $producer->email }}</span></div>
                    <div class="key-value"><span>Адрес</span><span>{{ $producer->address }}</span></div>
                    <div class="key-value"><span>Подтверждён</span><span>{{ $producer->approved_at?->translatedFormat('j F Y') ?? '—' }}</span></div>
                    <p class="description-text text-secondary mt-3 mb-0">{{ $producer->description }}</p>
                </div>
            </div>
            <div class="table-card">
                <div class="table-card__head"><h2>Последние заказы</h2></div>
                @if ($recentOrders->isEmpty())
                    <x-empty-state icon="receipt" title="Заказов нет" :compact="true" />
                @else
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                @foreach ($recentOrders as $order)
                                    <tr>
                                        <td><a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold">{{ $order->number }}</a></td>
                                        <td>{{ $order->buyer->name }}</td>
                                        <td class="text-nowrap">{{ money($order->total) }}</td>
                                        <td><x-status-badge :status="$order->status" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-xl-4">
            @if (auth()->user()->isAdmin())
                <div class="card mb-4">
                    <div class="card-header">Статус проверки</div>
                    <div class="card-body d-grid gap-2">
                        @if (! $producer->isApproved())
                            <form method="POST" action="{{ route('admin.producers.status', $producer) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="approved">
                                <button class="btn btn-primary w-100"><i class="bi bi-patch-check"></i> Подтвердить</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.producers.status', $producer) }}" data-confirm="Заблокировать «{{ $producer->name }}»? Все его товары будут скрыты из каталога." data-confirm-button="Заблокировать">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="rejected">
                                <button class="btn btn-outline-danger w-100"><i class="bi bi-slash-circle"></i> Заблокировать</button>
                            </form>
                        @endif
                        <div class="small text-secondary">Товары производителя видны в каталоге только при статусе «Подтверждён».</div>
                    </div>
                </div>
            @endif
            <div class="card mb-4">
                <div class="card-header">Торговые точки</div>
                <div class="card-body">
                    @forelse ($producer->locations as $location)
                        <div class="{{ $loop->last ? '' : 'mb-3' }}">
                            <div class="fw-semibold">{{ $location->name }} @if ($location->is_pickup_point)<span class="badge rounded-pill bg-primary-subtle text-primary-emphasis">самовывоз</span>@endif</div>
                            <div class="small text-secondary">{{ $location->address }}{{ $location->working_hours ? ' · '.$location->working_hours : '' }}</div>
                        </div>
                    @empty
                        <div class="small text-secondary">Точек нет.</div>
                    @endforelse
                </div>
            </div>
            <div class="card">
                <div class="card-header">История заявок</div>
                <div class="card-body">
                    @foreach ($producer->applications->sortByDesc('created_at') as $application)
                        <div class="{{ $loop->last ? '' : 'mb-3' }}">
                            <div class="d-flex justify-content-between"><span class="small">{{ $application->created_at->translatedFormat('j M Y') }}</span><x-status-badge :status="$application->status" /></div>
                            @if ($application->admin_comment)<div class="small text-secondary">{{ $application->admin_comment }}</div>@endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
