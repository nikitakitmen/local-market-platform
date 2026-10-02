@extends('layouts.admin')

@section('title', $promoCode->exists ? 'Редактирование промокода' : 'Новый промокод')

@section('content')
    <x-breadcrumbs :items="['Промокоды' => route('admin.promo-codes.index'), ($promoCode->exists ? $promoCode->code : 'Новый промокод') => null]" class="mb-3" />
    <div class="dash-page-head">
        <h1>{{ $promoCode->exists ? 'Промокод '.$promoCode->code : 'Новый промокод' }}</h1>
    </div>

    <div class="card" style="max-width: 760px">
        <div class="card-body">
            <form method="POST" action="{{ $promoCode->exists ? route('admin.promo-codes.update', $promoCode) : route('admin.promo-codes.store') }}" novalidate>
                @csrf
                @if ($promoCode->exists)
                    @method('PUT')
                @endif
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="code">Код</label>
                        <input id="code" type="text" name="code" value="{{ old('code', $promoCode->code) }}" class="form-control text-uppercase @error('code') is-invalid @enderror" placeholder="SPRING15">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="type">Тип скидки</label>
                        <select id="type" name="type" class="form-select @error('type') is-invalid @enderror">
                            @foreach (\App\Enums\PromoCodeType::cases() as $type)
                                <option value="{{ $type->value }}" @selected(old('type', $promoCode->type?->value) === $type->value)>{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="value">Размер скидки</label>
                        <input id="value" type="number" step="0.01" min="1" name="value" value="{{ old('value', $promoCode->value) }}" class="form-control @error('value') is-invalid @enderror">
                        <div class="form-text">Процент (до 90) или сумма в рублях.</div>
                        @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="min_order_amount">Минимальная сумма товаров, ₽</label>
                        <input id="min_order_amount" type="number" step="0.01" min="0" name="min_order_amount" value="{{ old('min_order_amount', $promoCode->min_order_amount) }}" class="form-control @error('min_order_amount') is-invalid @enderror" placeholder="Без ограничения">
                        @error('min_order_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="start_date">Дата начала</label>
                        <input id="start_date" type="date" name="start_date" value="{{ old('start_date', $promoCode->start_date?->format('Y-m-d')) }}" class="form-control @error('start_date') is-invalid @enderror">
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="end_date">Дата окончания</label>
                        <input id="end_date" type="date" name="end_date" value="{{ old('end_date', $promoCode->end_date?->format('Y-m-d')) }}" class="form-control @error('end_date') is-invalid @enderror">
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $promoCode->is_active))>
                            <label class="form-check-label" for="is_active">Промокод активен</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Сохранить</button>
                    <a href="{{ route('admin.promo-codes.index') }}" class="btn btn-light">Отмена</a>
                </div>
            </form>
        </div>
    </div>
@endsection
