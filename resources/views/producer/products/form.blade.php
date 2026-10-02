@extends('layouts.producer')

@section('title', $product->exists ? 'Редактирование товара' : 'Новый товар')

@section('content')
    @php
        $oldVariants = old('variants', $product->exists
            ? $product->variants->map(fn ($v) => ['id' => $v->id, 'name' => $v->name, 'price' => $v->price, 'in_stock' => $v->in_stock])->all()
            : []);
    @endphp

    <x-breadcrumbs :items="['Товары' => route('producer.products.index'), ($product->exists ? $product->name : 'Новый товар') => null]" class="mb-3" />

    <div class="dash-page-head">
        <h1>{{ $product->exists ? 'Редактирование товара' : 'Новый товар' }}</h1>
        @if ($product->exists)
            <a href="{{ route('products.show', $product->slug) }}" class="btn btn-light" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Открыть на сайте</a>
        @endif
    </div>

    <form method="POST" action="{{ $product->exists ? route('producer.products.update', $product) : route('producer.products.store') }}" enctype="multipart/form-data" novalidate>
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card mb-4">
                    <div class="card-header">Основное</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="name">Название</label>
                                <input id="name" type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control @error('name') is-invalid @enderror" placeholder="Например: Сыр «Качотта»">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="category_id">Категория</label>
                                <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">Выберите категорию</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="subcategory_id">Подкатегория</label>
                                <select id="subcategory_id" name="subcategory_id" class="form-select @error('subcategory_id') is-invalid @enderror" data-subcategory-for="#category_id">
                                    <option value="">Без подкатегории</option>
                                    @foreach ($categories as $category)
                                        @foreach ($category->children as $child)
                                            <option value="{{ $child->id }}" data-parent="{{ $category->id }}" @selected((int) old('subcategory_id', $product->subcategory_id) === $child->id)>{{ $child->name }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                                @error('subcategory_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="description">Описание</label>
                                <textarea id="description" name="description" rows="5" maxlength="5000" data-counter class="form-control @error('description') is-invalid @enderror" placeholder="Состав, вкус, способ хранения, особенности производства">{{ old('description', $product->description) }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">Цена и варианты</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="price">Цена, ₽</label>
                                <input id="price" type="number" step="0.01" min="1" name="price" value="{{ old('price', $product->exists && $product->variants->isEmpty() ? $product->price : '') }}" class="form-control @error('price') is-invalid @enderror">
                                <div class="form-text">Если добавлены варианты, цена берётся из них.</div>
                                @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="unit">Единица продажи</label>
                                <input id="unit" type="text" name="unit" value="{{ old('unit', $product->unit) }}" class="form-control @error('unit') is-invalid @enderror" placeholder="шт, 1 кг, буханка 700 г">
                                @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mt-4" data-variants-editor>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <div class="fw-bold">Варианты товара</div>
                                    <div class="small text-secondary">Например: «250 г» и «500 г» со своей ценой.</div>
                                </div>
                                <button type="button" class="btn btn-soft btn-sm" data-variant-add><i class="bi bi-plus-lg"></i> Добавить вариант</button>
                            </div>
                            <div class="small text-secondary py-2 d-none" data-variants-empty>Вариантов нет — товар продаётся по одной цене.</div>
                            <div class="d-grid gap-2" data-variants-rows>
                                @foreach ($oldVariants as $index => $variant)
                                    @include('producer.products.partials.variant-row', ['index' => $index, 'variant' => $variant])
                                @endforeach
                            </div>
                            @error('variants')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            <template>
                                @include('producer.products.partials.variant-row', ['index' => '__INDEX__', 'variant' => ['in_stock' => true]])
                            </template>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">Фотографии</div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-5">
                                <label class="form-label" for="image">Основное фото</label>
                                <div class="upload-preview mb-2 {{ $product->image ? 'has-image' : '' }}" id="imagePreview" style="width: 100%; height: 200px; {{ $product->image ? "background-image: url('".$product->image_url."')" : '' }}">
                                    <i class="bi bi-image"></i>
                                </div>
                                <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" data-preview="#imagePreview" class="form-control @error('image') is-invalid @enderror">
                                <div class="form-text">JPG, PNG, WEBP до 2 МБ. Без фото покажем заглушку.</div>
                                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-7">
                                <label class="form-label" for="gallery">Дополнительные фото</label>
                                @if ($product->exists && $product->images->isNotEmpty())
                                    <div class="thumb-grid mb-3">
                                        @foreach ($product->images as $image)
                                            <label class="thumb-item">
                                                <img src="{{ $image->url }}" alt="">
                                                <span class="thumb-remove"><input type="checkbox" name="delete_images[]" value="{{ $image->id }}" class="form-check-input m-0"> удалить</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                                <input id="gallery" type="file" name="gallery[]" multiple accept="image/jpeg,image/png,image/webp" class="form-control @error('gallery') is-invalid @enderror @error('gallery.*') is-invalid @enderror">
                                <div class="form-text">До 6 файлов за раз.</div>
                                @error('gallery')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @error('gallery.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card mb-4">
                    <div class="card-header">Публикация</div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $product->is_active))>
                            <label class="form-check-label fw-semibold" for="is_active">Показывать в каталоге</label>
                            <div class="form-text">Снимите галочку, чтобы временно скрыть товар.</div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="in_stock" name="in_stock" value="1" @checked(old('in_stock', $product->in_stock))>
                            <label class="form-check-label fw-semibold" for="in_stock">Есть в наличии</label>
                            <div class="form-text">Без складского учёта: только «есть» или «нет».</div>
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check2"></i> {{ $product->exists ? 'Сохранить изменения' : 'Опубликовать товар' }}</button>
                        <a href="{{ route('producer.products.index') }}" class="btn btn-light w-100 mt-2">Отмена</a>
                    </div>
                </div>
                @if ($product->exists)
                    <div class="card">
                        <div class="card-body small text-secondary">
                            <div class="key-value"><span>Продано</span><span>{{ $product->sales_count }} шт.</span></div>
                            <div class="key-value"><span>Рейтинг</span><span>{{ $product->reviews_count ? number_format($product->rating, 1, ',', '').' ('.$product->reviews_count.')' : '—' }}</span></div>
                            <div class="key-value"><span>Добавлен</span><span>{{ $product->created_at->translatedFormat('j F Y') }}</span></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </form>
@endsection
