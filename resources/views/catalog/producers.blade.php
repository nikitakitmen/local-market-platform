@extends('layouts.app')

@section('title', 'Производители')

@section('content')
    <div class="container py-4">
        <x-breadcrumbs :items="['Каталог' => route('catalog'), 'Производители' => null]" />

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3 mb-4">
            <div>
                <h1 class="h2 mb-1">Производители</h1>
                <p class="text-secondary mb-0">Фермы, пекарни, обжарщики, флористы и мастерские — {{ $producers->total() }} {{ plural($producers->total(), 'продавец', 'продавца', 'продавцов') }}</p>
            </div>
            @include('catalog.partials.switch', ['active' => 'producers'])
        </div>

        <form method="GET" action="{{ route('catalog.producers') }}" class="filters-card mb-4" data-no-loading>
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small" for="q">Поиск</label>
                    <input type="search" id="q" name="q" value="{{ $search }}" class="form-control" placeholder="Название или описание">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small" for="city">Город</label>
                    <select id="city" name="city" class="form-select">
                        <option value="">Все</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}" @selected((int) request('city') === $city->id)>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small" for="type">Тип</label>
                    <select id="type" name="type" class="form-select">
                        <option value="">Любой</option>
                        @foreach (\App\Enums\ProducerType::cases() as $type)
                            <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small" for="sort">Сортировка</label>
                    <select id="sort" name="sort" class="form-select">
                        <option value="rating" @selected($sort === 'rating')>По рейтингу</option>
                        <option value="products" @selected($sort === 'products')>По числу товаров</option>
                        <option value="new" @selected($sort === 'new')>Новые</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Найти</button>
                </div>
            </div>
        </form>

        @if ($producers->isEmpty())
            <x-empty-state icon="shop" title="Производители не найдены" text="Измените условия поиска или посмотрите всех производителей.">
                <a href="{{ route('catalog.producers') }}" class="btn btn-primary">Показать всех</a>
            </x-empty-state>
        @else
            <div class="row g-3 g-lg-4">
                @foreach ($producers as $producer)
                    <div class="col-md-6 col-xl-4">
                        <x-producer-card :producer="$producer" />
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $producers->links() }}</div>
        @endif
    </div>
@endsection
