@extends('layouts.account')

@section('title', $address->exists ? 'Редактирование адреса' : 'Новый адрес')

@section('page')
    <x-breadcrumbs :items="['Адреса' => route('account.addresses.index'), ($address->exists ? 'Редактирование' : 'Новый адрес') => null]" class="mb-3" />
    <div class="dash-page-head">
        <h1>{{ $address->exists ? 'Редактирование адреса' : 'Новый адрес' }}</h1>
    </div>

    <div class="card" style="max-width: 760px">
        <div class="card-body">
            <form method="POST" action="{{ $address->exists ? route('account.addresses.update', $address) : route('account.addresses.store') }}" novalidate>
                @csrf
                @if ($address->exists)
                    @method('PUT')
                @endif
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="title">Название</label>
                        <input id="title" type="text" name="title" value="{{ old('title', $address->title) }}" class="form-control @error('title') is-invalid @enderror" placeholder="Дом, Работа">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label" for="city_id">Город</label>
                        <select id="city_id" name="city_id" class="form-select @error('city_id') is-invalid @enderror">
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" @selected(old('city_id', $address->city_id ?? auth()->user()->city_id) == $city->id)>{{ $city->name }}</option>
                            @endforeach
                        </select>
                        @error('city_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="street">Улица и дом</label>
                        <input id="street" type="text" name="street" value="{{ old('street', $address->street) }}" class="form-control @error('street') is-invalid @enderror" placeholder="ул. Баумана, д. 15">
                        @error('street')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-4">
                        <label class="form-label" for="apartment">Квартира</label>
                        <input id="apartment" type="text" name="apartment" value="{{ old('apartment', $address->apartment) }}" class="form-control @error('apartment') is-invalid @enderror">
                    </div>
                    <div class="col-4">
                        <label class="form-label" for="entrance">Подъезд</label>
                        <input id="entrance" type="text" name="entrance" value="{{ old('entrance', $address->entrance) }}" class="form-control @error('entrance') is-invalid @enderror">
                    </div>
                    <div class="col-4">
                        <label class="form-label" for="floor">Этаж</label>
                        <input id="floor" type="text" name="floor" value="{{ old('floor', $address->floor) }}" class="form-control @error('floor') is-invalid @enderror">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="distance_km">Удалённость от центра</label>
                        <select id="distance_km" name="distance_km" class="form-select @error('distance_km') is-invalid @enderror">
                            @foreach (\App\Models\Address::DISTANCE_ZONES as $km => $label)
                                <option value="{{ $km }}" @selected((int) old('distance_km', $address->distance_km) === $km)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Нужна для расчёта стоимости доставки (без карт и GPS).</div>
                        @error('distance_km')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="comment">Комментарий курьеру</label>
                        <input id="comment" type="text" name="comment" value="{{ old('comment', $address->comment) }}" class="form-control @error('comment') is-invalid @enderror" placeholder="Код домофона, ориентир">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default" @checked(old('is_default', $address->is_default))>
                            <label class="form-check-label" for="is_default">Сделать основным адресом</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Сохранить</button>
                    <a href="{{ route('account.addresses.index') }}" class="btn btn-light">Отмена</a>
                </div>
            </form>
        </div>
    </div>
@endsection
