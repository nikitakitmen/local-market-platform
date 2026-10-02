@extends('layouts.app')

@section('title', 'Оформление заказа')

@section('content')
    @php
        $goodsTotal = $summary['total'];
        $oldMethod = old('delivery_method', 'delivery');
        $defaultAddress = $addresses->first();
        $oldAddress = old('address_id', $defaultAddress?->id ?? 'new');
    @endphp

    <div class="container py-4">
        <x-breadcrumbs :items="['Корзина' => route('cart.index'), 'Оформление заказа' => null]" class="mb-3" />
        <h1 class="h2 mb-4">Оформление заказа</h1>

        @error('cart')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        <form method="POST" action="{{ route('checkout.store') }}" id="checkoutForm" novalidate
              data-delivery-base="{{ $delivery['base'] }}"
              data-delivery-per-km="{{ $delivery['per_km'] }}"
              data-delivery-free-from="{{ $delivery['free_from'] }}"
              data-goods-total="{{ $goodsTotal }}">
            @csrf
            <div class="row g-4">
                <div class="col-lg-8">
                    {{-- 1. Способ получения --}}
                    <div class="checkout-step">
                        <div class="checkout-step__title"><span>1</span> Способ получения</div>
                        <div class="row g-3">
                            @foreach (\App\Enums\DeliveryMethod::cases() as $method)
                                <div class="col-sm-6">
                                    <label class="option-card">
                                        <input type="radio" name="delivery_method" value="{{ $method->value }}" @checked($oldMethod === $method->value)>
                                        <span class="option-card__body">
                                            <span class="option-card__icon"><i class="bi {{ $method->icon() }}"></i></span>
                                            <span>
                                                <span class="option-card__title">{{ $method->label() }}</span>
                                                <span class="option-card__text">
                                                    {{ $method === \App\Enums\DeliveryMethod::Delivery
                                                        ? 'Курьер платформы, '.money($delivery['base']).' + '.money($delivery['per_km']).'/км, бесплатно от '.money($delivery['free_from'])
                                                        : 'Заберите заказ в торговой точке производителя — бесплатно' }}
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        {{-- Адрес доставки --}}
                        <div class="mt-4 {{ $oldMethod === 'delivery' ? '' : 'd-none' }}" data-delivery-block>
                            <div class="fw-bold mb-2">Адрес доставки</div>
                            <div class="d-grid gap-2">
                                @foreach ($addresses as $address)
                                    <label class="option-card">
                                        <input type="radio" name="address_id" value="{{ $address->id }}" data-city="{{ $address->city_id }}" data-distance="{{ $address->distance_km }}" @checked((string) $oldAddress === (string) $address->id)>
                                        <span class="option-card__body py-2">
                                            <span class="option-card__icon"><i class="bi bi-geo-alt"></i></span>
                                            <span>
                                                <span class="option-card__title">{{ $address->title ?: 'Адрес' }} @if ($address->is_default)<span class="badge rounded-pill bg-primary-subtle text-primary-emphasis ms-1">основной</span>@endif</span>
                                                <span class="option-card__text">{{ $address->full_address }} · {{ $address->distance_label }}</span>
                                            </span>
                                        </span>
                                    </label>
                                @endforeach
                                <label class="option-card">
                                    <input type="radio" name="address_id" value="new" @checked($oldAddress === 'new')>
                                    <span class="option-card__body py-2">
                                        <span class="option-card__icon"><i class="bi bi-plus-lg"></i></span>
                                        <span><span class="option-card__title">Новый адрес</span><span class="option-card__text">Указать другой адрес доставки</span></span>
                                    </span>
                                </label>
                            </div>
                            @error('address_id')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                            <div class="alert alert-warning small mt-3 mb-0 d-none" data-city-warning>
                                <i class="bi bi-exclamation-triangle me-1"></i>Доставка работает только в пределах города производителя. Для товаров из другого города выберите самовывоз.
                            </div>

                            <div class="row g-3 mt-1 {{ $oldAddress === 'new' ? '' : 'd-none' }}" data-new-address>
                                <div class="col-md-4">
                                    <label class="form-label" for="new_city">Город</label>
                                    <select id="new_city" name="new_address[city_id]" class="form-select @error('new_address.city_id') is-invalid @enderror">
                                        @foreach ($cities as $city)
                                            <option value="{{ $city->id }}" @selected(old('new_address.city_id', $user->city_id ?? $summary['groups']->first()['producer']->city_id) == $city->id)>{{ $city->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('new_address.city_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label" for="new_street">Улица и дом</label>
                                    <input id="new_street" type="text" name="new_address[street]" value="{{ old('new_address.street') }}" class="form-control @error('new_address.street') is-invalid @enderror" placeholder="ул. Баумана, д. 15">
                                    @error('new_address.street')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-4 col-md-2">
                                    <label class="form-label" for="new_apartment">Кв.</label>
                                    <input id="new_apartment" type="text" name="new_address[apartment]" value="{{ old('new_address.apartment') }}" class="form-control">
                                </div>
                                <div class="col-4 col-md-2">
                                    <label class="form-label" for="new_entrance">Подъезд</label>
                                    <input id="new_entrance" type="text" name="new_address[entrance]" value="{{ old('new_address.entrance') }}" class="form-control">
                                </div>
                                <div class="col-4 col-md-2">
                                    <label class="form-label" for="new_floor">Этаж</label>
                                    <input id="new_floor" type="text" name="new_address[floor]" value="{{ old('new_address.floor') }}" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="new_distance">Удалённость</label>
                                    <select id="new_distance" name="new_address[distance_km]" class="form-select @error('new_address.distance_km') is-invalid @enderror">
                                        @foreach (\App\Models\Address::DISTANCE_ZONES as $km => $label)
                                            <option value="{{ $km }}" @selected((int) old('new_address.distance_km', 7) === $km)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Используется для расчёта стоимости доставки.</div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="save_address" value="1" id="saveAddress" @checked(old('save_address', true))>
                                        <label class="form-check-label" for="saveAddress">Сохранить адрес в профиле</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Пункты самовывоза для каждого производителя --}}
                        <div class="mt-4 {{ $oldMethod === 'pickup' ? '' : 'd-none' }}" data-pickup-block>
                            <div class="fw-bold mb-2">Пункты самовывоза</div>
                            @foreach ($summary['groups'] as $group)
                                @php($points = $group['producer']->locations->where('is_pickup_point', true))
                                <div class="mb-3">
                                    <label class="form-label small" for="pickup{{ $group['producer']->id }}">{{ $group['producer']->name }}</label>
                                    @if ($points->isEmpty())
                                        <div class="small text-danger">У производителя нет пунктов самовывоза — выберите доставку.</div>
                                    @else
                                        <select id="pickup{{ $group['producer']->id }}" name="pickup_locations[{{ $group['producer']->id }}]"
                                                class="form-select @error('pickup_locations.'.$group['producer']->id) is-invalid @enderror">
                                            @foreach ($points as $point)
                                                <option value="{{ $point->id }}" @selected(old('pickup_locations.'.$group['producer']->id) == $point->id)>
                                                    {{ $point->name }} — {{ $point->address }}{{ $point->working_hours ? ' ('.$point->working_hours.')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('pickup_locations.'.$group['producer']->id)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 2. Получатель --}}
                    <div class="checkout-step">
                        <div class="checkout-step__title"><span>2</span> Получатель</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="recipient_name">Имя</label>
                                <input id="recipient_name" type="text" name="recipient_name" value="{{ old('recipient_name', $user->name) }}" class="form-control @error('recipient_name') is-invalid @enderror">
                                @error('recipient_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="recipient_phone">Телефон</label>
                                <input id="recipient_phone" type="tel" name="recipient_phone" value="{{ old('recipient_phone', $user->phone) }}" class="form-control @error('recipient_phone') is-invalid @enderror">
                                @error('recipient_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="comment">Комментарий к заказу</label>
                                <textarea id="comment" name="comment" rows="2" maxlength="1000" class="form-control @error('comment') is-invalid @enderror" placeholder="Например: позвоните за 10 минут, домофон не работает">{{ old('comment') }}</textarea>
                                @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- 3. Оплата --}}
                    <div class="checkout-step">
                        <div class="checkout-step__title"><span>3</span> Способ оплаты</div>
                        <div class="row g-3">
                            @foreach (\App\Enums\PaymentMethod::cases() as $payment)
                                <div class="col-md-4">
                                    <label class="option-card">
                                        <input type="radio" name="payment_method" value="{{ $payment->value }}" @checked(old('payment_method', 'card') === $payment->value)>
                                        <span class="option-card__body">
                                            <span class="option-card__icon"><i class="bi {{ $payment->icon() }}"></i></span>
                                            <span>
                                                <span class="option-card__title">{{ $payment->label() }}</span>
                                                <span class="option-card__text">{{ $payment->isOnline() ? 'Онлайн, демо-режим' : 'Курьеру или в точке' }}</span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('payment_method')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        <div class="small text-secondary mt-3"><i class="bi bi-info-circle me-1"></i>Реальные платёжные системы не подключены: оплата картой и по СБП работает в демонстрационном режиме, данные карт не сохраняются.</div>
                    </div>
                </div>

                {{-- Итог: заказы по производителям --}}
                <div class="col-lg-4">
                    <div class="summary-card">
                        <h2 class="h5 mb-1">{{ $summary['groups']->count() > 1 ? 'Ваши заказы' : 'Ваш заказ' }}</h2>
                        @if ($summary['groups']->count() > 1)
                            <p class="small text-secondary mb-2">Будет создано {{ $summary['groups']->count() }} {{ plural($summary['groups']->count(), 'заказ', 'заказа', 'заказов') }} — по одному на производителя.</p>
                        @endif

                        @foreach ($summary['groups'] as $group)
                            <div class="checkout-order" data-order-group data-subtotal="{{ $group['subtotal'] }}" data-city="{{ $group['producer']->city_id }}">
                                <div class="d-flex justify-content-between gap-2 fw-bold small mb-1">
                                    <span class="text-truncate">{{ $group['producer']->name }}</span>
                                    <span class="text-nowrap">{{ money($group['subtotal']) }}</span>
                                </div>
                                @foreach ($group['items'] as $item)
                                    <div class="d-flex justify-content-between gap-2 small text-secondary">
                                        <span class="text-truncate">{{ $item->product->name }}{{ $item->variant ? ', '.$item->variant->name : '' }} × {{ $item->quantity }}</span>
                                        <span class="text-nowrap">{{ money($item->total) }}</span>
                                    </div>
                                @endforeach
                                <div class="d-flex justify-content-between small mt-1">
                                    <span class="text-secondary">Доставка</span>
                                    <span class="fw-semibold" data-delivery-cost>—</span>
                                </div>
                                <div class="small text-danger d-none" data-delivery-warning>Доставка только по г. {{ $group['producer']->city->name }}</div>
                            </div>
                        @endforeach

                        <div class="summary-row mt-2"><span>Товары</span><span>{{ money($summary['subtotal']) }}</span></div>
                        @if ($summary['discount'] > 0)
                            <div class="summary-row text-primary"><span>Промокод {{ $summary['promo']->code }}</span><span>−{{ money($summary['discount']) }}</span></div>
                        @endif
                        <div class="summary-row"><span>Доставка</span><span data-summary-delivery>—</span></div>
                        <div class="summary-total"><span>Итого</span><span data-summary-total>{{ money($goodsTotal) }}</span></div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 mt-3"><i class="bi bi-bag-check"></i> Оформить заказ</button>
                        <p class="small text-secondary mt-2 mb-0 text-center">Окончательная стоимость доставки рассчитывается на сервере при оформлении.</p>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
