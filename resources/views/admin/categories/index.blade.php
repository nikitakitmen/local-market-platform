@extends('layouts.admin')

@section('title', 'Категории')

@section('content')
    <div class="dash-page-head">
        <div>
            <h1>Категории</h1>
            <p>Двухуровневый каталог: категория → подкатегория.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Добавить категорию</a>
    </div>

    @if ($categories->isEmpty())
        <x-empty-state icon="tags" title="Категорий нет" text="Создайте первую категорию каталога.">
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Добавить категорию</a>
        </x-empty-state>
    @else
        <div class="row g-3">
            @foreach ($categories as $category)
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center gap-2">
                            <span class="d-flex align-items-center gap-2">
                                <span class="category-tile__icon" style="width: 2.25rem; height: 2.25rem; font-size: 1.05rem"><i class="bi bi-{{ $category->icon ?: 'tag' }}"></i></span>
                                {{ $category->name }}
                                @unless ($category->is_active)<span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">скрыта</span>@endunless
                            </span>
                            <span class="d-flex gap-1">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-light btn-icon btn-sm" title="Редактировать"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Удалить категорию «{{ $category->name }}» вместе с подкатегориями?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light btn-icon btn-sm text-danger" title="Удалить"><i class="bi bi-trash3"></i></button>
                                </form>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="small text-secondary mb-2">{{ $category->products_count }} {{ plural($category->products_count, 'товар', 'товара', 'товаров') }} · {{ $category->description }}</div>
                            @foreach ($category->children as $child)
                                <div class="d-flex justify-content-between align-items-center py-2 border-top">
                                    <span>{{ $child->name }} <span class="small text-secondary">({{ $child->subcategory_products_count }})</span>
                                        @unless ($child->is_active)<span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">скрыта</span>@endunless
                                    </span>
                                    <span class="d-flex gap-1">
                                        <a href="{{ route('admin.categories.edit', $child) }}" class="btn btn-light btn-icon btn-sm" title="Редактировать"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $child) }}" data-confirm="Удалить подкатегорию «{{ $child->name }}»?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light btn-icon btn-sm text-danger" title="Удалить"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    </span>
                                </div>
                            @endforeach
                            <a href="{{ route('admin.categories.create', ['parent' => $category->id]) }}" class="btn btn-soft btn-sm mt-2"><i class="bi bi-plus-lg"></i> Подкатегория</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
