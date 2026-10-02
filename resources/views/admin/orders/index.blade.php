@extends('layouts.admin')

@section('title', 'Заказы')

@section('content')
    <div class="dash-page-head">
        <div>
            <h1>Заказы</h1>
            <p>Все заказы платформы. Отфильтруйте проблемные, чтобы помочь покупателям.</p>
        </div>
    </div>

    <div class="status-tabs">
        <a href="{{ route('admin.orders.index') }}" class="{{ ! $status && ! $problem ? 'active' : '' }}">Все</a>
        @foreach (\App\Enums\OrderStatus::cases() as $case)
            <a href="{{ route('admin.orders.index', ['status' => $case->value]) }}" class="{{ $status === $case ? 'active' : '' }}">{{ $case->label() }}</a>
        @endforeach
        <a href="{{ route('admin.orders.index', ['problem' => 1]) }}" class="{{ $problem ? 'active' : '' }}">
            <i class="bi bi-exclamation-triangle"></i> Проблемные <span class="badge rounded-pill text-bg-danger">{{ $problemCount }}</span>
        </a>
    </div>

    <div class="table-card">
        <div class="table-card__head">
            <form method="GET" class="filter-bar w-100" data-no-loading>
                @if ($status)<input type="hidden" name="status" value="{{ $status->value }}">@endif
                @if ($problem)<input type="hidden" name="problem" value="1">@endif
                <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Номер, имя или email покупателя">
                <select name="producer" class="form-select" data-autosubmit>
                    <option value="">Все производители</option>
                    @foreach ($producers as $producer)
                        <option value="{{ $producer->id }}" @selected((int) request('producer') === $producer->id)>{{ $producer->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-light"><i class="bi bi-search"></i> Найти</button>
            </form>
        </div>
        @include('admin.orders.partials.table')
        @if ($orders->hasPages())
            <div class="table-card__foot">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
