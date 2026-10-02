{{-- Строка варианта товара в редакторе (используется и как шаблон для JS) --}}
<div class="row g-2 align-items-center" data-variant-row>
    @if (! empty($variant['id']))
        <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant['id'] }}">
    @endif
    <div class="col-5">
        <input type="text" name="variants[{{ $index }}][name]" value="{{ $variant['name'] ?? '' }}" class="form-control form-control-sm @error('variants.'.$index.'.name') is-invalid @enderror" placeholder="Название, напр. 500 г" aria-label="Название варианта">
    </div>
    <div class="col-3">
        <input type="number" step="0.01" min="1" name="variants[{{ $index }}][price]" value="{{ $variant['price'] ?? '' }}" class="form-control form-control-sm @error('variants.'.$index.'.price') is-invalid @enderror" placeholder="Цена" aria-label="Цена варианта">
    </div>
    <div class="col-3">
        <div class="form-check form-switch m-0">
            <input class="form-check-input" type="checkbox" role="switch" name="variants[{{ $index }}][in_stock]" value="1" id="variantStock{{ $index }}" @checked(! empty($variant['in_stock']))>
            <label class="form-check-label small" for="variantStock{{ $index }}">в наличии</label>
        </div>
    </div>
    <div class="col-1 text-end">
        <button type="button" class="btn btn-light btn-icon btn-sm text-danger" data-variant-remove aria-label="Удалить вариант"><i class="bi bi-x-lg"></i></button>
    </div>
</div>
