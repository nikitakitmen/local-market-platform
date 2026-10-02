@extends('layouts.admin')

@section('title', 'Товары')

@section('content')
    <div class="dash-page-head">
        <div>
            <h1>Товары</h1>
            <p>Модерация товаров: скрыть нарушающий правила товар или удалить его.</p>
        </div>
    </div>

    <div class="table-card">
        <div class="table-card__head">
            <form method="GET" class="filter-bar w-100" data-no-loading>
                <input type="search" name="q" value="{{ $search }}" class="form-control" placeholder="Название товара">
                <select name="producer" class="form-select" data-autosubmit>
                    <option value="">Все производители</option>
                    @foreach ($producers as $producer)
                        <option value="{{ $producer->id }}" @selected((int) request('producer') === $producer->id)>{{ $producer->name }}</option>
                    @endforeach
                </select>
                <select name="category" class="form-select" data-autosubmit>
                    <option value="">Все категории</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) request('category') === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="visibility" class="form-select" data-autosubmit>
                    <option value="">Любая видимость</option>
                    <option value="active" @selected(request('visibility') === 'active')>Опубликованные</option>
                    <option value="hidden" @selected(request('visibility') === 'hidden')>Скрытые</option>
                </select>
                <button type="submit" class="btn btn-light"><i class="bi bi-search"></i></button>
            </form>
        </div>
        @if ($products->isEmpty())
            <x-empty-state icon="box-seam" title="Товары не найдены" :compact="true" />
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Товар</th><th>Производитель</th><th>Категория</th><th>Цена</th><th>Наличие</th><th>Видимость</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $product->image_url }}" alt="" class="table-thumb">
                                        <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="fw-semibold text-reset">{{ $product->name }}</a>
                                    </div>
                                </td>
                                <td class="small">{{ $product->producer->name }}</td>
                                <td class="small">{{ $product->category->name }}</td>
                                <td class="text-nowrap fw-semibold">{{ $product->variants->isNotEmpty() ? 'от ' : '' }}{{ money($product->price) }}</td>
                                <td>{!! $product->in_stock ? '<span class="badge rounded-pill bg-success-subtle text-success-emphasis">есть</span>' : '<span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">нет</span>' !!}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.products.toggle', $product) }}" data-no-loading>
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="badge rounded-pill border-0 {{ $product->is_active ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">
                                            {{ $product->is_active ? 'Опубликован' : 'Скрыт' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Удалить товар «{{ $product->name }}»?">
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
