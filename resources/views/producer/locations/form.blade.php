@extends('layouts.producer')

@section('title', $location->exists ? 'Редактирование точки' : 'Новая точка')

@section('content')
    <x-breadcrumbs :items="['Торговые точки' => route('producer.locations.index'), ($location->exists ? $location->name : 'Новая точка') => null]" class="mb-3" />
    <div class="dash-page-head">
        <h1>{{ $location->exists ? 'Редактирование точки' : 'Новая торговая точка' }}</h1>
    </div>

    <div class="card" style="max-width: 760px">
        <div class="card-body">
            <form method="POST" action="{{ $location->exists ? route('producer.locations.update', $location) : route('producer.locations.store') }}" novalidate>
                @csrf
                @if ($location->exists)
                    @method('PUT')
                @endif
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="name">Название</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $location->name) }}" class="form-control @error('name') is-invalid @enderror" placeholder="Лавка на Центральном рынке">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="address">Адрес</label>
                        <input id="address" type="text" name="address" value="{{ old('address', $location->address) }}" class="form-control @error('address') is-invalid @enderror" placeholder="г. Казань, ул. Московская, 6">
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="working_hours">Часы работы</label>
                        <input id="working_hours" type="text" name="working_hours" value="{{ old('working_hours', $location->working_hours) }}" class="form-control @error('working_hours') is-invalid @enderror" placeholder="Ежедневно 9:00–20:00">
                        @error('working_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Телефон</label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone', $location->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_pickup_point" name="is_pickup_point" value="1" @checked(old('is_pickup_point', $location->is_pickup_point))>
                            <label class="form-check-label" for="is_pickup_point">Пункт самовывоза — покупатели могут забирать здесь заказы</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Сохранить</button>
                    <a href="{{ route('producer.locations.index') }}" class="btn btn-light">Отмена</a>
                </div>
            </form>
        </div>
    </div>
@endsection
