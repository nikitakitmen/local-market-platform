@extends('layouts.admin')

@section('title', 'Производители')

@section('content')
    <div class="dash-page-head">
        <div>
            <h1>Производители</h1>
            <p>Магазины платформы и статус их проверки.</p>
        </div>
        @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.applications.index') }}" class="btn btn-light"><i class="bi bi-person-badge"></i> Заявки</a>
        @endif
    </div>

    <div class="status-tabs">
        <a href="{{ route('admin.producers.index') }}" class="{{ $status ? '' : 'active' }}">Все</a>
        @foreach (\App\Enums\ProducerStatus::cases() as $case)
            <a href="{{ route('admin.producers.index', ['status' => $case->value]) }}" class="{{ $status === $case ? 'active' : '' }}">{{ $case->label() }}</a>
        @endforeach
    </div>

    <div class="table-card">
        <div class="table-card__head">
            <form method="GET" class="filter-bar" data-no-loading>
                @if ($status)<input type="hidden" name="status" value="{{ $status->value }}">@endif
                <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Название">
                <button class="btn btn-light" type="submit"><i class="bi bi-search"></i></button>
            </form>
        </div>
        @if ($producers->isEmpty())
            <x-empty-state icon="shop" title="Производители не найдены" :compact="true" />
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Производитель</th><th>Тип</th><th>Город</th><th>Товары</th><th>Заказы</th><th>Рейтинг</th><th>Статус</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($producers as $producer)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <x-producer-logo :producer="$producer" size="sm" />
                                        <div class="min-w-0">
                                            <div class="fw-semibold">{{ $producer->name }}</div>
                                            <div class="small text-secondary">{{ $producer->user->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="small">{{ $producer->type->label() }}</td>
                                <td class="small">{{ $producer->city->name }}</td>
                                <td>{{ $producer->products_count }}</td>
                                <td>{{ $producer->orders_count }}</td>
                                <td class="small text-nowrap">@if ($producer->reviews_count)<i class="bi bi-star-fill text-warning"></i> {{ number_format($producer->rating, 1, ',', '') }}@else — @endif</td>
                                <td><x-status-badge :status="$producer->status" /></td>
                                <td class="text-end"><a href="{{ route('admin.producers.show', $producer) }}" class="btn btn-light btn-sm">Открыть</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($producers->hasPages())
                <div class="table-card__foot">{{ $producers->links() }}</div>
            @endif
        @endif
    </div>
@endsection
