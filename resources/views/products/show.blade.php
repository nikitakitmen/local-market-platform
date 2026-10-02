@extends('layouts.app')

@section('title', $product->name)
@section('description', \Illuminate\Support\Str::limit($product->description, 160))

@section('content')
    @php
        $hasVariants = $product->hasVariants();
        $defaultVariant = $product->defaultVariant();
        $available = $product->isAvailable();
        $canBuy = ! $isOwner && (! auth()->check() || auth()->user()->canShop());
        $gallery = collect([$product->image_url])->merge($product->images->pluck('url'));

        $crumbs = ['Каталог' => route('catalog'), $product->category->name => route('catalog', ['category' => $product->category->slug])];
        if ($product->subcategory) {
            $crumbs[$product->subcategory->name] = route('catalog', ['category' => $product->category->slug, 'subcategory' => $product->subcategory->slug]);
        }
        $crumbs[$product->name] = null;
    @endphp

    <div class="container py-4">
        <x-breadcrumbs :items="$crumbs" class="mb-3" />

        @if (! $product->is_active || ! $product->producer->isApproved())
            <div class="alert alert-warning"><i class="bi bi-eye-slash me-2"></i>Товар скрыт и не виден покупателям в каталоге.</div>
        @endif

        <div class="row g-4 g-xl-5">
            {{-- Фотографии --}}
            <div class="col-lg-6">
                <div class="product-gallery" data-gallery>
                    <div class="product-gallery__main">
                        <img src="{{ $gallery->first() }}" alt="{{ $product->name }}" data-gallery-main>
                    </div>
                    @if ($gallery->count() > 1)
                        <div class="product-gallery__thumbs">
                            @foreach ($gallery as $url)
                                <button type="button" class="product-gallery__thumb {{ $loop->first ? 'active' : '' }}" data-gallery-thumb data-src="{{ $url }}" aria-label="Фото {{ $loop->iteration }}">
                                    <img src="{{ $url }}" alt="" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Информация и покупка --}}
            <div class="col-lg-6 product-info">
                <a href="{{ route('producers.show', $product->producer->slug) }}" class="fw-semibold small">{{ $product->producer->name }}</a>
                <h1 class="product-title mt-1 mb-2">{{ $product->name }}</h1>

                <div class="d-flex flex-wrap align-items-center gap-3 mb-3 small">
                    @if ($product->reviews_count)
                        <a href="#reviews" class="text-reset"><x-rating :value="$product->rating" :count="$product->reviews_count" /></a>
                    @else
                        <span class="text-secondary"><i class="bi bi-star me-1"></i>Пока нет отзывов</span>
                    @endif
                    @if ($product->sales_count)
                        <span class="text-secondary"><i class="bi bi-bag-check me-1"></i>Купили {{ $product->sales_count }} {{ plural($product->sales_count, 'раз', 'раза', 'раз') }}</span>
                    @endif
                    @if ($available)
                        <span class="badge rounded-pill bg-success-subtle text-success-emphasis"><i class="bi bi-check2 me-1"></i>В наличии</span>
                    @else
                        <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">Нет в наличии</span>
                    @endif
                </div>

                <div class="buy-box" data-variant-scope>
                    <div class="product-price mb-3">
                        <span data-variant-price>{{ money($defaultVariant?->price ?? $product->price) }}</span>
                        @if ($product->unit && ! $hasVariants)
                            <small>/ {{ $product->unit }}</small>
                        @endif
                    </div>

                    @if ($isOwner)
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-1"></i> Это ваш товар.
                            <a href="{{ route('producer.products.edit', $product) }}" class="fw-semibold">Редактировать</a>
                        </div>
                    @elseif ($canBuy)
                        <form action="{{ route('cart.store') }}" method="POST" data-cart-form>
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            @if ($hasVariants)
                                <div class="mb-3">
                                    <div class="small fw-semibold text-secondary mb-2">Вариант</div>
                                    <div class="variant-options">
                                        @foreach ($product->variants as $variant)
                                            <input type="radio" name="variant_id" id="variant{{ $variant->id }}" value="{{ $variant->id }}"
                                                   data-variant-input data-price="{{ money($variant->price) }}"
                                                   @checked($variant->id === $defaultVariant?->id) @disabled(! $variant->in_stock)>
                                            <label for="variant{{ $variant->id }}">
                                                {{ $variant->name }}
                                                <small>{{ $variant->in_stock ? money($variant->price) : 'нет в наличии' }}</small>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <div class="qty-stepper qty-stepper-lg">
                                    <button type="button" data-qty-step="-1" aria-label="Меньше"><i class="bi bi-dash"></i></button>
                                    <input type="number" name="quantity" value="1" min="1" max="99" class="no-spin" aria-label="Количество">
                                    <button type="button" data-qty-step="1" aria-label="Больше"><i class="bi bi-plus"></i></button>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg flex-grow-1 btn-cart" @disabled(! $available)>
                                    <i class="bi bi-bag-plus"></i><span>{{ $available ? 'В корзину' : 'Нет в наличии' }}</span>
                                </button>
                                <x-favorite-button :product="$product" :static="true" />
                            </div>
                        </form>
                    @else
                        <div class="text-secondary small"><i class="bi bi-info-circle me-1"></i>Покупки доступны покупателям.</div>
                    @endif
                </div>

                <a href="{{ route('producers.show', $product->producer->slug) }}" class="producer-mini mt-3">
                    <x-producer-logo :producer="$product->producer" size="sm" />
                    <span class="min-w-0 flex-grow-1">
                        <span class="fw-bold d-block text-truncate">{{ $product->producer->name }}</span>
                        <span class="small text-secondary">{{ $product->producer->type->label() }} · {{ $product->producer->city->name }}
                            @if ($product->producer->reviews_count) · <i class="bi bi-star-fill text-warning"></i> {{ number_format($product->producer->rating, 1, ',', '') }} @endif
                        </span>
                    </span>
                    <i class="bi bi-chevron-right text-secondary"></i>
                </a>

                @if ($canBuy && auth()->check())
                    <form method="POST" action="{{ route('chat.start', $product->producer) }}" class="mt-2">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <button type="submit" class="btn btn-soft w-100"><i class="bi bi-chat-dots"></i> Задать вопрос производителю</button>
                    </form>
                @endif

                <ul class="info-list mt-3">
                    <li><i class="bi bi-truck"></i><span>Доставка курьером по г. {{ $product->producer->city->name }} — бесплатно от {{ money(\App\Models\Setting::get('delivery_free_from')) }}</span></li>
                    <li><i class="bi bi-shop"></i><span>Самовывоз из торговых точек производителя</span></li>
                    <li><i class="bi bi-credit-card"></i><span>Оплата картой, по СБП или наличными при получении</span></li>
                </ul>
            </div>
        </div>

        {{-- Описание и характеристики --}}
        <div class="row g-4 mt-2">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Описание</h2>
                        <p class="description-text mb-0">{{ $product->description ?: 'Производитель пока не добавил описание.' }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Характеристики</h2>
                        <dl class="spec-list">
                            <dt>Категория</dt><dd>{{ $product->category->name }}</dd>
                            @if ($product->subcategory)
                                <dt>Подкатегория</dt><dd>{{ $product->subcategory->name }}</dd>
                            @endif
                            <dt>Производитель</dt><dd>{{ $product->producer->name }}</dd>
                            @if ($product->unit)
                                <dt>Единица</dt><dd>{{ $product->unit }}</dd>
                            @endif
                            @if ($hasVariants)
                                <dt>Варианты</dt><dd>{{ $product->variants->pluck('name')->implode(', ') }}</dd>
                            @endif
                            <dt>Город</dt><dd>{{ $product->producer->city->name }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Отзывы --}}
        <section id="reviews" class="mt-5">
            <div class="section-header">
                <h2>Отзывы <span class="text-secondary fw-semibold">{{ $product->reviews_count }}</span></h2>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="rating-summary mb-3">
                                <div class="rating-summary__value">{{ $product->reviews_count ? number_format($product->rating, 1, ',', '') : '—' }}</div>
                                <div>
                                    <x-rating :value="$product->rating" size="lg" :show-value="false" />
                                    <div class="small text-secondary">{{ $product->reviews_count }} {{ plural($product->reviews_count, 'оценка', 'оценки', 'оценок') }}</div>
                                </div>
                            </div>
                            <div class="rating-bars d-grid gap-1">
                                @for ($star = 5; $star >= 1; $star--)
                                    @php($count = (int) ($ratingCounts[$star] ?? 0))
                                    <div class="rating-bar">
                                        <span style="width: 1.5rem">{{ $star }} <i class="bi bi-star-fill text-warning"></i></span>
                                        <div class="progress"><div class="progress-bar" style="width: {{ $product->reviews_count ? $count / $product->reviews_count * 100 : 0 }}%"></div></div>
                                        <span style="width: 1.5rem" class="text-end">{{ $count }}</span>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-body">
                            @if ($canReview)
                                <h3 class="h6 mb-3">Оцените товар</h3>
                                <form method="POST" action="{{ route('reviews.store', $product) }}" novalidate>
                                    @csrf
                                    <div class="rating-input mb-2">
                                        @for ($i = 5; $i >= 1; $i--)
                                            <input type="radio" name="rating" id="star{{ $i }}" value="{{ $i }}" @checked((int) old('rating') === $i)>
                                            <label for="star{{ $i }}" title="{{ $i }}"><i class="bi bi-star-fill"></i></label>
                                        @endfor
                                    </div>
                                    @error('rating')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                                    <textarea name="text" rows="4" maxlength="2000" data-counter class="form-control @error('text') is-invalid @enderror" placeholder="Что понравилось, а что нет?">{{ old('text') }}</textarea>
                                    @error('text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <button type="submit" class="btn btn-primary w-100 mt-3">Опубликовать отзыв</button>
                                </form>
                            @elseif ($userReview)
                                <div class="small"><i class="bi bi-check2-circle text-primary me-1"></i>Вы уже оставили отзыв на этот товар{{ $userReview->is_hidden ? ' (скрыт модератором)' : '' }}.</div>
                            @else
                                <div class="small text-secondary">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Оставить отзыв можно после получения заказа с этим товаром.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body py-2">
                            @forelse ($reviews as $review)
                                @include('products.partials.review', ['review' => $review])
                            @empty
                                <x-empty-state icon="chat-square-text" title="Отзывов пока нет" text="Купите товар и станьте первым, кто поделится впечатлениями." :compact="true" />
                            @endforelse
                        </div>
                    </div>
                    <div class="mt-3">{{ $reviews->links() }}</div>
                </div>
            </div>
        </section>

        {{-- Похожие товары --}}
        @if ($similar->isNotEmpty())
            <section class="mt-5">
                <div class="section-header">
                    <h2>Похожие товары</h2>
                    <a href="{{ route('catalog', ['category' => $product->category->slug]) }}" class="link-arrow">Вся категория <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="row g-3 g-lg-4">
                    @foreach ($similar as $item)
                        <div class="col-6 col-md-4 col-lg-3">
                            <x-product-card :product="$item" />
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
