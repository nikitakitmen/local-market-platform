@extends('layouts.producer')

@section('title', 'Торговые точки')

@section('content')
    <div class="dash-page-head">
        <div>
            <h1>Торговые точки</h1>
            <p>Магазины, лавки и места на рынке. Точки с отметкой «самовывоз» доступны покупателям при оформлении заказа.</p>
        </div>
        <a href="{{ route('producer.locations.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Добавить точку</a>
    </div>

    @if ($locations->isEmpty())
        <x-empty-state icon="geo-alt" title="Точек пока нет" text="Добавьте торговую точку, чтобы покупатели могли забирать заказы самостоятельно.">
            <a href="{{ route('producer.locations.create') }}" class="btn btn-primary">Добавить точку</a>
        </x-empty-state>
    @else
        <div class="row g-3">
            @foreach ($locations as $location)
                <div class="col-md-6 col-xxl-4">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between gap-2 mb-2">
                                <div class="fw-bold">{{ $location->name }}</div>
                                @if ($location->is_pickup_point)
                                    <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis align-self-start">Самовывоз</span>
                                @endif
                            </div>
                            <ul class="info-list mb-3">
                                <li><i class="bi bi-geo-alt"></i><span>{{ $location->address }}</span></li>
                                @if ($location->working_hours)<li><i class="bi bi-clock"></i><span>{{ $location->working_hours }}</span></li>@endif
                                @if ($location->phone)<li><i class="bi bi-telephone"></i><span>{{ $location->phone }}</span></li>@endif
                            </ul>
                            <div class="d-flex gap-2 mt-auto">
                                <a href="{{ route('producer.locations.edit', $location) }}" class="btn btn-light btn-sm"><i class="bi bi-pencil"></i> Изменить</a>
                                <form method="POST" action="{{ route('producer.locations.destroy', $location) }}" data-confirm="Удалить точку «{{ $location->name }}»?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-sm text-danger"><i class="bi bi-trash3"></i> Удалить</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
