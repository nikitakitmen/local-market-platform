{{-- Поля анкеты производителя: заявка «Стать производителем» и настройки кабинета --}}
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label" for="name">Название</label>
        <input id="name" type="text" name="name" value="{{ old('name', $producer?->name) }}" class="form-control @error('name') is-invalid @enderror" placeholder="Например: Ферма «Зелёный луг»" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="type">Тип</label>
        <select id="type" name="type" class="form-select @error('type') is-invalid @enderror">
            @foreach (\App\Enums\ProducerType::cases() as $type)
                <option value="{{ $type->value }}" @selected(old('type', $producer?->type?->value) === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="inn">ИНН <span class="text-secondary fw-normal">(необязательно)</span></label>
        <input id="inn" type="text" name="inn" value="{{ old('inn', $producer?->inn) }}" class="form-control @error('inn') is-invalid @enderror" inputmode="numeric" maxlength="12">
        @error('inn')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="phone">Телефон</label>
        <input id="phone" type="tel" name="phone" value="{{ old('phone', $producer?->phone ?? auth()->user()->phone) }}" class="form-control @error('phone') is-invalid @enderror">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="email">Email для покупателей</label>
        <input id="email" type="email" name="email" value="{{ old('email', $producer?->email ?? auth()->user()->email) }}" class="form-control @error('email') is-invalid @enderror">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label" for="city_id">Город</label>
        <select id="city_id" name="city_id" class="form-select @error('city_id') is-invalid @enderror">
            @foreach ($cities as $city)
                <option value="{{ $city->id }}" @selected(old('city_id', $producer?->city_id ?? auth()->user()->city_id) == $city->id)>{{ $city->name }}</option>
            @endforeach
        </select>
        @error('city_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8">
        <label class="form-label" for="address">Адрес производства или магазина</label>
        <input id="address" type="text" name="address" value="{{ old('address', $producer?->address) }}" class="form-control @error('address') is-invalid @enderror">
        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="description">Описание</label>
        <textarea id="description" name="description" rows="4" maxlength="3000" data-counter class="form-control @error('description') is-invalid @enderror" placeholder="Что вы производите, чем гордитесь, почему стоит выбрать вас">{{ old('description', $producer?->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label" for="logo">Логотип</label>
        <div class="d-flex align-items-center gap-3">
            <div class="upload-preview {{ $producer?->logo_url ? 'has-image' : '' }}" id="logoPreview"
                 @if ($producer?->logo_url) style="background-image: url('{{ $producer->logo_url }}')" @endif>
                <i class="bi bi-image"></i>
            </div>
            <div class="flex-grow-1">
                <input id="logo" type="file" name="logo" accept="image/jpeg,image/png,image/webp" data-preview="#logoPreview" class="form-control @error('logo') is-invalid @enderror">
                <div class="form-text">JPG, PNG или WEBP, до 2 МБ.</div>
                @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>
