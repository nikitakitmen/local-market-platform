{{-- Содержимое корзины. Перерисовывается целиком после AJAX-изменений количества. --}}
@if ($summary['groups']->isEmpty())
    <x-empty-state icon="bag" title="Корзина пуста" text="Загляните в каталог — там сыры, хлеб на закваске, свежий кофе и многое другое.">
        <a href="{{ route('catalog') }}" class="btn btn-primary"><i class="bi bi-grid"></i> Перейти в каталог</a>
    </x-empty-state>
@else
    <div class="row g-4">
        <div class="col-lg-8">
            @if ($summary['groups']->count() > 1)
                <div class="alert alert-info d-flex gap-2 align-items-start">
                    <i class="bi bi-info-circle mt-1"></i>
                    <div>В корзине товары {{ $summary['groups']->count() }} производителей — при оформлении будет создано {{ $summary['groups']->count() }} {{ plural($summary['groups']->count(), 'заказ', 'заказа', 'заказов') }}, по одному на каждого продавца.</div>
                </div>
            @endif

            @foreach ($summary['groups'] as $group)
                <div class="cart-group">
                    <div class="cart-group__head">
                        <a href="{{ route('producers.show', $group['producer']->slug) }}" class="d-flex align-items-center gap-2 text-reset min-w-0">
                            <x-producer-logo :producer="$group['producer']" size="sm" />
                            <span class="min-w-0">
                                <span class="fw-bold d-block text-truncate">{{ $group['producer']->name }}</span>
                                <span class="small text-secondary">г. {{ $group['producer']->city->name }}</span>
                            </span>
                        </a>
                        <span class="fw-bold text-nowrap">{{ money($group['subtotal']) }}</span>
                    </div>

                    @foreach ($group['items'] as $item)
                        @php($available = $item->isAvailable())
                        <div class="cart-item {{ $available ? '' : 'is-unavailable' }}">
                            <a href="{{ route('products.show', $item->product->slug) }}" class="cart-item__image">
                                <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" loading="lazy">
                            </a>
                            <div class="min-w-0">
                                <a href="{{ route('products.show', $item->product->slug) }}" class="cart-item__name d-block">{{ $item->product->name }}</a>
                                <div class="small text-secondary">
                                    @if ($item->variant){{ $item->variant->name }} · @endif
                                    {{ money($item->unit_price) }}{{ ! $item->variant && $item->product->unit ? ' / '.$item->product->unit : '' }}
                                </div>
                                @unless ($available)
                                    <div class="small text-danger fw-semibold mt-1"><i class="bi bi-exclamation-circle"></i> Нет в наличии — удалите из корзины</div>
                                @endunless
                            </div>
                            <div class="cart-item__controls">
                                <form action="{{ route('cart.update', $item) }}" method="POST" data-cart-ajax data-no-loading>
                                    @csrf
                                    @method('PATCH')
                                    <div class="qty-stepper">
                                        <button type="button" data-qty-step="-1" aria-label="Меньше" @disabled($item->quantity <= 1)><i class="bi bi-dash"></i></button>
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="99" class="no-spin" data-qty-input aria-label="Количество">
                                        <button type="button" data-qty-step="1" aria-label="Больше"><i class="bi bi-plus"></i></button>
                                    </div>
                                    <noscript><button type="submit" class="btn btn-sm btn-light mt-1">Обновить</button></noscript>
                                </form>
                                <div class="cart-item__total">{{ money($item->total) }}</div>
                                <form action="{{ route('cart.destroy', $item) }}" method="POST" data-cart-ajax data-no-loading>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-icon btn-sm" title="Удалить" aria-label="Удалить"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </div>
                    @endforeach

                    <div class="cart-group__foot d-flex justify-content-between flex-wrap gap-2">
                        @if ($group['missing'] > 0)
                            <span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>До минимальной суммы заказа ({{ money($summary['min_order']) }}) не хватает {{ money($group['missing']) }}</span>
                            <a href="{{ route('producers.show', $group['producer']->slug) }}" class="fw-semibold">Добавить товары</a>
                        @else
                            <span class="text-secondary"><i class="bi bi-check2-circle text-primary me-1"></i>Будет оформлен отдельным заказом</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="col-lg-4">
            <div class="summary-card">
                <h2 class="h5 mb-3">Ваш заказ</h2>
                <div class="summary-row"><span>Товары ({{ $summary['count'] }})</span><span>{{ money($summary['subtotal']) }}</span></div>
                @if ($summary['discount'] > 0)
                    <div class="summary-row text-primary"><span>Скидка по промокоду</span><span>−{{ money($summary['discount']) }}</span></div>
                @endif
                <div class="summary-row"><span>Доставка</span><span class="text-secondary">при оформлении</span></div>
                <div class="summary-total"><span>Итого</span><span>{{ money($summary['total']) }}</span></div>

                <div class="mt-3">
                    @if ($summary['promo'])
                        <div class="d-flex justify-content-between align-items-center p-2 rounded-3 {{ $summary['promo_error'] ? 'bg-danger-subtle' : 'bg-primary-soft' }}">
                            <span class="small fw-semibold">
                                <i class="bi bi-ticket-perforated me-1"></i>{{ $summary['promo']->code }}
                                <span class="text-secondary">{{ $summary['promo']->label }}</span>
                            </span>
                            <form method="POST" action="{{ route('cart.promo.remove') }}" data-no-loading>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link btn-sm p-0 text-secondary" title="Удалить промокод"><i class="bi bi-x-lg"></i></button>
                            </form>
                        </div>
                        @if ($summary['promo_error'])
                            <div class="small text-danger mt-1">{{ $summary['promo_error'] }}</div>
                        @endif
                    @else
                        <form method="POST" action="{{ route('cart.promo.apply') }}" class="input-group">
                            @csrf
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" placeholder="Промокод" value="{{ old('code') }}" aria-label="Промокод">
                            <button class="btn btn-outline-secondary" type="submit">Применить</button>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </form>
                    @endif
                </div>

                @if ($summary['can_checkout'])
                    <a href="{{ route('checkout.create') }}" class="btn btn-primary btn-lg w-100 mt-3">Перейти к оформлению <i class="bi bi-arrow-right"></i></a>
                @else
                    <button class="btn btn-primary btn-lg w-100 mt-3" disabled>Перейти к оформлению</button>
                    <div class="small text-secondary mt-2 text-center">
                        {{ $summary['has_unavailable'] ? 'Удалите товары, которых нет в наличии.' : 'Наберите минимальную сумму заказа у каждого производителя.' }}
                    </div>
                @endif
                <div class="small text-secondary mt-3"><i class="bi bi-shield-check me-1"></i>Оплата при получении, картой или по СБП</div>
            </div>
        </div>
    </div>
@endif
