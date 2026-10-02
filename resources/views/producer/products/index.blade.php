@extends('layouts.producer')

@section('title', 'Товары')

@section('content')
    @include('producer.partials.status-banner')

    <div class="dash-page-head">
        <div>
            <h1>Товары</h1>
            <p>Управляйте ассортиментом, наличием и видимостью товаров.</p>
        </div>
        @can('create', \App\Models\Product::class)
            <a href="{{ route('producer.products.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Добавить товар</a>
        @endcan
    </div>

    <div class="table-card">
        <div class="table-card__head">
            <div class="status-tabs mb-0">
                @foreach (['' => 'Все', 'active' => 'Опубликованные', 'hidden' => 'Скрытые', 'out' => 'Нет в наличии'] as $value => $label)
                    <a href="{{ route('producer.products.index', array_filter(['filter' => $value, 'q' => $search])) }}" class="{{ (string) $filter === (string) $value ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>
            <form method="GET" class="filter-bar" data-no-loading>
                @if ($filter)<input type="hidden" name="filter" value="{{ $filter }}">@endif
                <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Поиск по названию">
            </form>
        </div>

        @if ($products->isEmpty())
            <x-empty-state icon="box-seam" title="Товаров нет" text="Добавьте первый товар — он сразу появится в каталоге." :compact="true">
                @can('create', \App\Models\Product::class)
                    <a href="{{ route('producer.products.create') }}" class="btn btn-primary">Добавить товар</a>
                @endcan
            </x-empty-state>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr><th>Товар</th><th>Категория</th><th>Цена</th><th>Наличие</th><th>Видимость</th><th>Рейтинг</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $product->image_url }}" alt="" class="table-thumb">
                                        <div class="min-w-0">
                                            <a href="{{ route('producer.products.edit', $product) }}" class="fw-semibold text-reset d-block text-truncate" style="max-width: 240px">{{ $product->name }}</a>
                                            <div class="small text-secondary">
                                                {{ $product->variants->isNotEmpty() ? $product->variants->count().' '.plural($product->variants->count(), 'вариант', 'варианта', 'вариантов') : ($product->unit ?: '—') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="small">{{ $product->category->name }}@if ($product->subcategory)<br><span class="text-secondary">{{ $product->subcategory->name }}</span>@endif</td>
                                <td class="fw-semibold text-nowrap">{{ $product->variants->isNotEmpty() ? 'от ' : '' }}{{ money($product->price) }}</td>
                                <td>
                                    <form method="POST" action="{{ route('producer.products.toggle-stock', $product) }}" data-no-loading>
                                        @csrf
                                        @method('PATCH')
                                        <div class="form-check form-switch m-0" title="Есть в наличии">
                                            <input class="form-check-input" type="checkbox" role="switch" @checked($product->in_stock) data-autosubmit aria-label="В наличии">
                                        </div>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('producer.products.toggle-active', $product) }}" data-no-loading>
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="badge rounded-pill border-0 {{ $product->is_active ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}" title="Нажмите, чтобы {{ $product->is_active ? 'скрыть' : 'показать' }}">
                                            <i class="bi {{ $product->is_active ? 'bi-eye' : 'bi-eye-slash' }} me-1"></i>{{ $product->is_active ? 'Опубликован' : 'Скрыт' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="small text-nowrap">
                                    @if ($product->reviews_count)
                                        <i class="bi bi-star-fill text-warning"></i> {{ number_format($product->rating, 1, ',', '') }} <span class="text-secondary">({{ $product->reviews_count }})</span>
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('products.show', $product->slug) }}" class="btn btn-light btn-icon btn-sm" title="Открыть на сайте" target="_blank"><i class="bi bi-box-arrow-up-right"></i></a>
                                    <a href="{{ route('producer.products.edit', $product) }}" class="btn btn-light btn-icon btn-sm" title="Редактировать"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('producer.products.destroy', $product) }}" class="d-inline" data-confirm="Удалить товар «{{ $product->name }}»? Он исчезнет из каталога.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-light btn-icon btn-sm text-danger" title="Удалить"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($products->hasPages())
                <div class="table-card__foot">{{ $products->links() }}</div>
            @endif
        @endif
    </div>
@endsection
