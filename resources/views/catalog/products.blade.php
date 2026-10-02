@extends('layouts.app')

@section('title', $subcategory?->name ?? $category?->name ?? 'Каталог товаров')

@section('content')
    <div class="container py-4">
        @php
            $crumbs = ['Каталог' => route('catalog')];
            if ($category) {
                $crumbs[$category->name] = route('catalog', ['category' => $category->slug]);
            }
            if ($subcategory) {
                $crumbs[$subcategory->name] = null;
            }
        @endphp
        <x-breadcrumbs :items="$crumbs" />

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3 mb-4">
            <div>
                <h1 class="h2 mb-1">{{ $subcategory?->name ?? $category?->name ?? 'Каталог товаров' }}</h1>
                <p class="text-secondary mb-0">
                    Найдено {{ $products->total() }} {{ plural($products->total(), 'товар', 'товара', 'товаров') }}
                </p>
            </div>
            @include('catalog.partials.switch', ['active' => 'products'])
        </div>

        <div class="row g-4">
            <div class="col-lg-3">
                <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="catalogFilters" aria-labelledby="catalogFiltersLabel">
                    <div class="offcanvas-header border-bottom">
                        <h5 class="offcanvas-title fw-bold" id="catalogFiltersLabel">Фильтры</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#catalogFilters" aria-label="Закрыть"></button>
                    </div>
                    <div class="offcanvas-body d-block p-3 p-lg-0">
                        @include('catalog.partials.filters')
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="catalog-toolbar">
                    <button class="btn btn-light d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#catalogFilters" aria-controls="catalogFilters">
                        <i class="bi bi-sliders"></i> Фильтры
                        @if (count($chips))
                            <span class="badge rounded-pill text-bg-primary">{{ count($chips) }}</span>
                        @endif
                    </button>

                    <form method="GET" action="{{ route('catalog') }}" class="ms-auto d-flex align-items-center gap-2" data-no-loading>
                        @foreach (request()->except(['sort', 'page']) as $key => $value)
                            @if (is_string($value))
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <label for="sort" class="text-secondary small d-none d-sm-inline text-nowrap">Сортировка:</label>
                        <select name="sort" id="sort" class="form-select" data-autosubmit>
                            @foreach (\App\Models\Product::SORTS as $value => $label)
                                <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                @if (count($chips))
                    <div class="filter-chips">
                        @foreach ($chips as $chip)
                            <span class="filter-chip">{{ $chip['label'] }} <a href="{{ $chip['url'] }}" aria-label="Убрать фильтр"><i class="bi bi-x"></i></a></span>
                        @endforeach
                        <a href="{{ route('catalog') }}" class="small fw-semibold align-self-center ms-1">Сбросить всё</a>
                    </div>
                @endif

                @if ($products->isEmpty())
                    <x-empty-state icon="search" title="Ничего не найдено"
                                   text="Попробуйте изменить запрос или сбросить часть фильтров — возможно, нужный товар есть в другой категории.">
                        <a href="{{ route('catalog') }}" class="btn btn-primary">Сбросить фильтры</a>
                    </x-empty-state>
                @else
                    <div class="row g-3 g-lg-4">
                        @foreach ($products as $product)
                            <div class="col-6 col-md-4">
                                <x-product-card :product="$product" />
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
