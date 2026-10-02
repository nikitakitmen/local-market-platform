{{-- Личный кабинет покупателя: меню слева, содержимое справа --}}
@extends('layouts.app')

@section('content')
    <div class="container py-4 py-lg-5">
        <div class="row g-4">
            <div class="col-lg-3">
                @include('account.partials.nav')
            </div>
            <div class="col-lg-9 min-w-0">
                @yield('page')
            </div>
        </div>
    </div>
@endsection
