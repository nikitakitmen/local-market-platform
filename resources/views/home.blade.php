@extends('layouts.app')

@section('title', 'Товары от местных производителей с доставкой')

@section('content')
    {{-- Hero: на первом экране понятно, что это локальный маркетплейс с доставкой --}}
    <section class="hero">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 fade-in-up">
                    <span class="hero-eyebrow"><span>Новое</span> Локальный маркетплейс малых производителей</span>
                    <h1 class="hero-title">Свежее и&nbsp;настоящее — <mark>от&nbsp;местных мастеров</mark> с&nbsp;доставкой до&nbsp;двери</h1>
                    <p class="hero-lead">
                        Фермерские продукты, выпечка, кофе, цветы и изделия ручной работы напрямую от производителей вашего города.
                        Закажите онлайн — курьер привезёт, или заберите сами.
                    </p>

                    <form action="{{ route('catalog') }}" method="GET" class="hero-search" role="search" data-no-loading>
                        <i class="bi bi-search"></i>
                        <input type="search" name="q" class="form-control" placeholder="Найдите сыр, мёд, круассаны или керамику" aria-label="Поиск товаров">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search d-sm-none"></i><span>Найти</span></button>
                    </form>

                    <div class="hero-tags">
                        <span>Часто ищут:</span>
                        @foreach (['Сыр', 'Хлеб', 'Кофе', 'Мёд', 'Букет'] as $tag)
                            <a href="{{ route('catalog', ['q' => $tag]) }}">{{ $tag }}</a>
                        @endforeach
                    </div>

                    <div class="hero-stats">
                        <div>
                            <div class="hero-stat__value">{{ $stats['producers'] }}</div>
                            <div class="hero-stat__label">{{ plural($stats['producers'], 'производитель', 'производителя', 'производителей') }}</div>
                        </div>
                        <div>
                            <div class="hero-stat__value">{{ $stats['products'] }}</div>
                            <div class="hero-stat__label">{{ plural($stats['products'], 'товар', 'товара', 'товаров') }} в каталоге</div>
                        </div>
                        <div>
                            <div class="hero-stat__value">{{ $stats['cities'] }}</div>
                            <div class="hero-stat__label">{{ plural($stats['cities'], 'город', 'города', 'городов') }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 d-none d-lg-block">
                    <div class="hero-visual">
                        @foreach ($popular->take(3) as $item)
                            <a href="{{ route('products.show', $item->slug) }}" class="hero-visual__card text-reset">
                                <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
                                <div class="hero-visual__caption">
                                    {{ $item->name }}
                                    <small>{{ $item->producer->name }} · {{ money($item->hasVariants() ? $item->defaultVariant()->price : $item->price) }}</small>
                                </div>
                            </a>
                        @endforeach
                        <div class="hero-visual__badge">
                            <i class="bi bi-truck"></i>
                            <div>Доставка курьером<small>бесплатно от {{ money(\App\Models\Setting::get('delivery_free_from')) }}</small></div>
                        </div>
                        <div class="hero-visual__rating"><i class="bi bi-patch-check-fill text-primary me-1"></i>Проверенные продавцы</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Категории --}}
    <section class="section pb-0">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2>Категории</h2>
                    <p class="section-subtitle">Не только продукты — всё, что делают с душой рядом с вами</p>
                </div>
                <a href="{{ route('catalog') }}" class="link-arrow d-none d-sm-inline">Весь каталог <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="row g-3">
                @foreach ($categories as $category)
                    <div class="col-6 col-md-4 col-lg-2">
                        <a href="{{ route('catalog', ['category' => $category->slug]) }}" class="category-tile">
                            <span class="category-tile__icon"><i class="bi bi-{{ $category->icon ?: 'tag' }}"></i></span>
                            <span>
                                <span class="category-tile__name d-block">{{ $category->name }}</span>
                                <span class="category-tile__count">{{ $category->products_count }} {{ plural($category->products_count, 'товар', 'товара', 'товаров') }}</span>
                            </span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Популярные товары --}}
    <section class="section pb-0">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2>Популярные товары</h2>
                    <p class="section-subtitle">Чаще всего заказывают на этой неделе</p>
                </div>
                <a href="{{ route('catalog', ['sort' => 'popular']) }}" class="link-arrow">Смотреть все <i class="bi bi-arrow-right"></i></a>
            </div>
            @if ($popular->isEmpty())
                <x-empty-state icon="box-seam" title="Товаров пока нет" text="Скоро здесь появятся товары местных производителей." />
            @else
                <div class="row g-3 g-lg-4">
                    @foreach ($popular as $product)
                        <div class="col-6 col-md-4 col-lg-3">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Новые товары --}}
    @if ($newest->isNotEmpty())
        <section class="section pb-0">
            <div class="container">
                <div class="section-header">
                    <div>
                        <h2>Новинки</h2>
                        <p class="section-subtitle">Свежие поступления от производителей</p>
                    </div>
                    <a href="{{ route('catalog', ['sort' => 'new']) }}" class="link-arrow">Все новинки <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="row g-3 g-lg-4">
                    @foreach ($newest->take(4) as $product)
                        <div class="col-6 col-md-4 col-lg-3">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Популярные производители --}}
    @if ($producers->isNotEmpty())
        <section class="section pb-0">
            <div class="container">
                <div class="section-header">
                    <div>
                        <h2>Производители</h2>
                        <p class="section-subtitle">Фермы, пекарни и мастерские с лучшими отзывами</p>
                    </div>
                    <a href="{{ route('catalog.producers') }}" class="link-arrow">Все производители <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="row g-3 g-lg-4">
                    @foreach ($producers as $producer)
                        <div class="col-md-6 col-lg-3">
                            <x-producer-card :producer="$producer" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Преимущества платформы --}}
    <section class="section">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2>Почему здесь удобно</h2>
                    <p class="section-subtitle">Покупайте напрямую у тех, кто производит</p>
                </div>
            </div>
            <div class="row g-3 g-lg-4">
                @foreach ([
                    ['shop', 'Только локальные производители', 'Каждый продавец проходит проверку администратором платформы перед публикацией товаров.'],
                    ['truck', 'Доставка курьером', 'Курьеры платформы привезут заказ в удобное время. Можно забрать самостоятельно.'],
                    ['cart-check', 'Одна корзина — разные продавцы', 'Добавляйте товары разных производителей: заказы разделятся автоматически.'],
                    ['chat-heart', 'Честные отзывы', 'Отзыв можно оставить только после выполненного заказа. Задавайте вопросы продавцу в чате.'],
                ] as [$icon, $title, $text])
                    <div class="col-sm-6 col-lg-3">
                        <div class="feature-item">
                            <div class="feature-item__icon"><i class="bi bi-{{ $icon }}"></i></div>
                            <h3>{{ $title }}</h3>
                            <p>{{ $text }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-4 mt-2 align-items-center">
                <div class="col-lg-5">
                    <h2 class="h4 mb-3">Как это работает</h2>
                    <div class="d-grid gap-4">
                        <div class="step-item"><span class="step-item__num">1</span><h3>Выберите товары</h3><p>Ищите по названию, фильтруйте по категориям, цене и рейтингу.</p></div>
                        <div class="step-item"><span class="step-item__num">2</span><h3>Оформите заказ</h3><p>Доставка или самовывоз, оплата наличными, картой или по СБП.</p></div>
                        <div class="step-item"><span class="step-item__num">3</span><h3>Получите и оцените</h3><p>Следите за статусом в личном кабинете и оставьте отзыв.</p></div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="cta-banner">
                        <h2>Вы производитель или мастер?</h2>
                        <p class="mb-4">Продавайте свои товары жителям города: подайте заявку, получите подтверждение и управляйте товарами, заказами и аналитикой в удобном кабинете.</p>
                        <a href="{{ route('producer-application.create') }}" class="btn btn-light btn-lg"><i class="bi bi-patch-plus"></i> Стать производителем</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
