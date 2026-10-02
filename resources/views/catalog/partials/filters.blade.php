{{-- Фильтры каталога товаров (на телефоне открываются в выезжающей панели) --}}
<form method="GET" action="{{ route('catalog') }}" class="filters-card" data-no-loading>
    @if ($sort !== 'popular')
        <input type="hidden" name="sort" value="{{ $sort }}">
    @endif

    <div class="filter-group">
        <div class="filter-title">Поиск</div>
        <div class="position-relative">
            <input type="search" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Название товара">
        </div>
    </div>

    <div class="filter-group">
        <div class="filter-title">Категория</div>
        <select name="category" id="filterCategory" class="form-select mb-2" aria-label="Категория">
            <option value="">Все категории</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->slug }}" @selected($category?->id === $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="subcategory" class="form-select" data-subcategory-for="#filterCategory" aria-label="Подкатегория">
            <option value="">Все подкатегории</option>
            @foreach ($categories as $cat)
                @foreach ($cat->children as $child)
                    <option value="{{ $child->slug }}" data-parent="{{ $cat->slug }}" @selected($subcategory?->id === $child->id)>{{ $child->name }}</option>
                @endforeach
            @endforeach
        </select>
    </div>

    <div class="filter-group">
        <div class="filter-title">Цена, ₽</div>
        <div class="d-flex gap-2">
            <input type="number" name="price_min" value="{{ $filters['price_min'] }}" min="0" class="form-control no-spin" placeholder="от" aria-label="Цена от">
            <input type="number" name="price_max" value="{{ $filters['price_max'] }}" min="0" class="form-control no-spin" placeholder="до" aria-label="Цена до">
        </div>
    </div>

    <div class="filter-group">
        <div class="filter-title">Производитель</div>
        <select name="producer" class="form-select" aria-label="Производитель">
            <option value="">Все производители</option>
            @foreach ($producers as $producerOption)
                <option value="{{ $producerOption->id }}" @selected($filters['producer_id'] === $producerOption->id)>{{ $producerOption->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="filter-group">
        <div class="filter-title">Город</div>
        <select name="city" class="form-select" aria-label="Город">
            <option value="">Все города</option>
            @foreach ($cities as $city)
                <option value="{{ $city->id }}" @selected($filters['city_id'] === $city->id)>{{ $city->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="filter-group">
        <div class="filter-title">Рейтинг</div>
        @foreach (['' => 'Любой', '3' => '3 и выше', '4' => '4 и выше', '4.5' => '4,5 и выше'] as $value => $label)
            <div class="form-check">
                <input class="form-check-input" type="radio" name="rating" id="rating{{ $loop->index }}" value="{{ $value }}"
                       @checked((string) ($filters['rating'] ?? '') === (string) $value)>
                <label class="form-check-label" for="rating{{ $loop->index }}">
                    @if ($value)<i class="bi bi-star-fill text-warning me-1"></i>@endif{{ $label }}
                </label>
            </div>
        @endforeach
    </div>

    <div class="filter-group">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" name="in_stock" value="1" id="inStock" @checked($filters['in_stock'])>
            <label class="form-check-label fw-semibold" for="inStock">Только в наличии</label>
        </div>
    </div>

    <div class="filter-group d-grid gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Показать</button>
        <a href="{{ route('catalog') }}" class="btn btn-light">Сбросить фильтры</a>
    </div>
</form>
