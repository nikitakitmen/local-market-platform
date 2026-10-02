@extends('layouts.producer')

@section('title', 'Заказы')

@section('content')
    <div class="dash-page-head">
        <div>
            <h1>Заказы</h1>
            <p>Принимайте новые заказы — после этого их увидят курьеры (для доставки).</p>
        </div>
        <form method="GET" class="filter-bar" data-no-loading>
            @if ($status)<input type="hidden" name="status" value="{{ $status->value }}">@endif
            <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Номер или покупатель">
            <button class="btn btn-light" type="submit"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <div class="status-tabs">
        <a href="{{ route('producer.orders.index') }}" class="{{ $status ? '' : 'active' }}">Все <span class="badge rounded-pill text-bg-light">{{ $counts->sum() }}</span></a>
        @foreach (\App\Enums\OrderStatus::cases() as $case)
            <a href="{{ route('producer.orders.index', ['status' => $case->value]) }}" class="{{ $status === $case ? 'active' : '' }}">
                {{ $case->label() }} <span class="badge rounded-pill text-bg-light">{{ $counts[$case->value] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="table-card">
        @include('producer.orders.partials.table')
        @if ($orders->hasPages())
            <div class="table-card__foot">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
