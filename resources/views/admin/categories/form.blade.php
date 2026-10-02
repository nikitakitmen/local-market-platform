@extends('layouts.admin')

@section('title', $category->exists ? 'Редактирование категории' : 'Новая категория')

@section('content')
    <x-breadcrumbs :items="['Категории' => route('admin.categories.index'), ($category->exists ? $category->name : 'Новая категория') => null]" class="mb-3" />
    <div class="dash-page-head">
        <h1>{{ $category->exists ? 'Редактирование категории' : 'Новая категория' }}</h1>
    </div>

    <div class="card" style="max-width: 760px">
        <div class="card-body">
            <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" novalidate>
                @csrf
                @if ($category->exists)
                    @method('PUT')
                @endif
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label" for="name">Название</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $category->name) }}" class="form-control @error('name') is-invalid @enderror">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="parent_id">Родительская категория</label>
                        <select id="parent_id" name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                            <option value="">— Корневая категория —</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}" @selected((int) old('parent_id', $category->parent_id) === $parent->id)>{{ $parent->name }}</option>
                            @endforeach
                        </select>
                        @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-7">
                        <label class="form-label" for="icon">Иконка (Bootstrap Icons)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-{{ old('icon', $category->icon) ?: 'tag' }}"></i></span>
                            <input id="icon" type="text" name="icon" value="{{ old('icon', $category->icon) }}" class="form-control @error('icon') is-invalid @enderror" placeholder="basket, cup-hot, flower1">
                            @error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-text">Название иконки с сайта icons.getbootstrap.com, без префикса «bi-».</div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="sort_order">Порядок сортировки</label>
                        <input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="form-control @error('sort_order') is-invalid @enderror">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="description">Описание</label>
                        <textarea id="description" name="description" rows="2" class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $category->is_active))>
                            <label class="form-check-label" for="is_active">Показывать в каталоге</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Сохранить</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-light">Отмена</a>
                </div>
            </form>
        </div>
    </div>
@endsection
