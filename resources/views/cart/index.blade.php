@extends('layouts.app')

@section('title', 'Корзина')

@section('content')
    <div class="container py-4">
        <x-breadcrumbs :items="['Корзина' => null]" class="mb-3" />
        <div class="d-flex align-items-baseline gap-3 mb-4">
            <h1 class="h2 mb-0">Корзина</h1>
        </div>

        <div id="cartContent">
            @include('cart.partials.content')
        </div>
    </div>
@endsection
