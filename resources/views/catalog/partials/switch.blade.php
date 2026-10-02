{{-- Переключатель каталога: Товары / Производители --}}
<div class="catalog-switch" role="tablist">
    <a href="{{ route('catalog') }}" class="{{ $active === 'products' ? 'active' : '' }}" role="tab"><i class="bi bi-box-seam me-1"></i>Товары</a>
    <a href="{{ route('catalog.producers') }}" class="{{ $active === 'producers' ? 'active' : '' }}" role="tab"><i class="bi bi-shop me-1"></i>Производители</a>
</div>
