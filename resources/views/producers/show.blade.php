@extends('layouts.app')

@section('title', $producer->name)
@section('description', \Illuminate\Support\Str::limit($producer->description, 160))

@section('content')
    @php($canMessage = auth()->check() && auth()->user()->canShop() && auth()->user()->producer?->id !== $producer->id)

    <div class="container py-4">
        <x-breadcrumbs :items="['Производители' => route('catalog.producers'), $producer->name => null]" class="mb-3" />

        @unless ($producer->isApproved())
            <div class="alert alert-warning"><i class="bi bi-hourglass-split me-2"></i>Профиль производителя ещё не подтверждён и не виден покупателям.</div>
        @endunless

        <div class="producer-hero mb-4">
            <div class="producer-hero__cover"></div>
            <div class="producer-hero__body">
                <div class="d-flex flex-column flex-md-row align-items-md-end gap-3">
                    <x-producer-logo :producer="$producer" size="lg" />
                    <div class="flex-grow-1 min-w-0 pt-md-5">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h1 class="h3 mb-0">{{ $producer->name }}</h1>
                            @if ($producer->isApproved())
                                <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis"><i class="bi bi-patch-check-fill me-1"></i>Проверенный продавец</span>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-3 small text-secondary">
                            <span>{{ $producer->type->label() }}</span>
                            <span><i class="bi bi-geo-alt me-1"></i>{{ $producer->city->name }}</span>
                            @if ($producer->reviews_count)
                                <x-rating :value="$producer->rating" :count="$producer->reviews_count" />
                            @endif
                            <span><i class="bi bi-bag-check me-1"></i>{{ $completedOrders }} {{ plural($completedOrders, 'выполненный заказ', 'выполненных заказа', 'выполненных заказов') }}</span>
                        </div>
                    </div>
                    @if ($canMessage)
                        <form method="POST" action="{{ route('chat.start', $producer) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary"><i class="bi bi-chat-dots"></i> Написать</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h2 class="h6 mb-2">О производителе</h2>
                        <p class="description-text text-secondary mb-0">{{ $producer->description ?: 'Описание пока не добавлено.' }}</p>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h2 class="h6 mb-2">Контакты</h2>
                        <ul class="info-list">
                            <li><i class="bi bi-geo-alt"></i><span>г. {{ $producer->city->name }}, {{ $producer->address }}</span></li>
                            <li><i class="bi bi-telephone"></i><a href="tel:{{ preg_replace('/[^\d+]/', '', $producer->phone) }}">{{ $producer->phone }}</a></li>
                            <li><i class="bi bi-envelope"></i><a href="mailto:{{ $producer->email }}">{{ $producer->email }}</a></li>
                            @if ($producer->inn)
                                <li><i class="bi bi-file-earmark-text"></i><span>ИНН {{ $producer->inn }}</span></li>
                            @endif
                        </ul>
                    </div>
                </div>

                @if ($producer->locations->isNotEmpty())
                    <h2 class="h6 mb-2 mt-4">Торговые точки</h2>
                    <div class="d-grid gap-2">
                        @foreach ($producer->locations as $location)
                            <div class="location-card">
                                <div class="d-flex justify-content-between gap-2">
                                    <span class="fw-bold">{{ $location->name }}</span>
                                    @if ($location->is_pickup_point)
                                        <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis align-self-start">Самовывоз</span>
                                    @endif
                                </div>
                                <div class="location-card__row"><i class="bi bi-geo-alt"></i>{{ $location->address }}</div>
                                @if ($location->working_hours)
                                    <div class="location-card__row"><i class="bi bi-clock"></i>{{ $location->working_hours }}</div>
                                @endif
                                @if ($location->phone)
                                    <div class="location-card__row"><i class="bi bi-telephone"></i>{{ $location->phone }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="col-lg-8">
                <section id="products">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h2 class="h5 mb-0">Товары <span class="text-secondary">{{ $products->total() }}</span></h2>
                        <form method="GET" data-no-loading>
                            <select name="sort" class="form-select form-select-sm" data-autosubmit aria-label="Сортировка">
                                @foreach (\App\Models\Product::SORTS as $value => $label)
                                    <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    @if ($products->isEmpty())
                        <x-empty-state icon="box-seam" title="Товаров пока нет" text="Производитель ещё не опубликовал товары." />
                    @else
                        <div class="row g-3">
                            @foreach ($products as $product)
                                <div class="col-6 col-xl-4">
                                    <x-product-card :product="$product" />
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">{{ $products->links() }}</div>
                    @endif
                </section>

                <section class="mt-5">
                    <h2 class="h5 mb-3">Отзывы покупателей</h2>
                    <div class="card">
                        <div class="card-body py-2">
                            @forelse ($reviews as $review)
                                @include('products.partials.review', ['review' => $review, 'showProduct' => true])
                            @empty
                                <x-empty-state icon="chat-square-text" title="Отзывов пока нет" text="Отзывы появятся после первых выполненных заказов." :compact="true" />
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
