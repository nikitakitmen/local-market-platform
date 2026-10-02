@extends('layouts.account')

@section('title', 'Избранное')

@section('page')
    <div class="dash-page-head">
        <div>
            <h1>Избранное</h1>
            <p>Товары, которые вы отметили сердечком.</p>
        </div>
    </div>

    @if ($products->isEmpty())
        <x-empty-state icon="heart" title="В избранном пусто" text="Нажмите на сердечко на карточке товара, чтобы сохранить его здесь.">
            <a href="{{ route('catalog') }}" class="btn btn-primary">Перейти в каталог</a>
        </x-empty-state>
    @else
        <div class="row g-3">
            @foreach ($products as $product)
                <div class="col-6 col-md-4" data-favorite-card>
                    <x-product-card :product="$product" />
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $products->links() }}</div>
    @endif
@endsection
