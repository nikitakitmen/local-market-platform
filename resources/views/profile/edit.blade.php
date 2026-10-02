{{-- Профиль открывается в кабинете своей роли (покупатель, производитель, курьер, сотрудник) --}}
@extends(auth()->user()->panelLayout())

@section('title', 'Профиль')

@section('page')
    <div class="dash-page-head">
        <div>
            <h1>Настройки профиля</h1>
            <p>Личные данные и пароль от аккаунта.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card">
                <div class="card-header">Личные данные</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="name">Имя</label>
                                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="phone">Телефон</label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control @error('phone') is-invalid @enderror" required>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="city_id">Город</label>
                                <select id="city_id" name="city_id" class="form-select @error('city_id') is-invalid @enderror">
                                    <option value="">Не выбран</option>
                                    @foreach ($cities as $city)
                                        <option value="{{ $city->id }}" @selected(old('city_id', $user->city_id) == $city->id)>{{ $city->name }}</option>
                                    @endforeach
                                </select>
                                @if ($user->isCourier())
                                    <div class="form-text">Вы будете видеть доставки только в этом городе.</div>
                                @endif
                                @error('city_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Роль</label>
                                <input type="text" class="form-control" value="{{ $user->role->label() }}" disabled>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-4"><i class="bi bi-check2"></i> Сохранить</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card">
                <div class="card-header">Смена пароля</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.password') }}" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label" for="current_password">Текущий пароль</label>
                            <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password">
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">Новый пароль</label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password_confirmation">Повторите новый пароль</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password">
                        </div>
                        <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-shield-lock"></i> Изменить пароль</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
