@extends('layouts.producer')

@section('title', 'Настройки')

@section('content')
    @include('producer.partials.status-banner')

    <div class="dash-page-head">
        <div>
            <h1>Настройки магазина</h1>
            <p>Эти данные видят покупатели на странице производителя.</p>
        </div>
        <x-status-badge :status="$producer->status" class="fs-6" />
    </div>

    <div class="card" style="max-width: 960px">
        <div class="card-body">
            <form method="POST" action="{{ route('producer.settings.update') }}" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')
                @include('producer.partials.profile-fields', ['producer' => $producer])
                <button type="submit" class="btn btn-primary mt-4"><i class="bi bi-check2"></i> Сохранить</button>
            </form>
        </div>
    </div>
@endsection
