<?php

namespace App\Http\Controllers;

use App\Enums\ProducerType;
use App\Models\Category;
use App\Models\City;
use App\Models\Producer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Каталог с переключателем «Товары / Производители».
 * Поиск — обычный LIKE-запрос MySQL, фильтры — scopes модели Product.
 */
class CatalogController extends Controller
{
    public function products(Request $request): View
    {
        $categories = Category::roots()->active()->ordered()->with(['children' => fn ($q) => $q->active()])->get();
        $category = $request->filled('category') ? $categories->firstWhere('slug', $request->query('category')) : null;
        $subcategory = $category && $request->filled('subcategory')
            ? $category->children->firstWhere('slug', $request->query('subcategory'))
            : null;

        $filters = [
            'q' => trim((string) $request->query('q')) ?: null,
            'category_id' => $category?->id,
            'subcategory_id' => $subcategory?->id,
            'price_min' => is_numeric($request->query('price_min')) ? (float) $request->query('price_min') : null,
            'price_max' => is_numeric($request->query('price_max')) ? (float) $request->query('price_max') : null,
            'producer_id' => (int) $request->query('producer') ?: null,
            'city_id' => (int) $request->query('city') ?: null,
            'rating' => in_array($request->query('rating'), ['3', '4', '4.5'], true) ? (float) $request->query('rating') : null,
            'in_stock' => $request->boolean('in_stock'),
        ];

        $sort = array_key_exists((string) $request->query('sort'), Product::SORTS) ? $request->query('sort') : 'popular';

        $products = Product::visible()
            ->filter($filters)
            ->sort($sort)
            ->with(['producer', 'variants'])
            ->paginate(12)
            ->withQueryString();

        $producers = Producer::approved()->orderBy('name')->get(['id', 'name']);
        $cities = City::active()->get();

        return view('catalog.products', [
            'products' => $products,
            'categories' => $categories,
            'category' => $category,
            'subcategory' => $subcategory,
            'producers' => $producers,
            'cities' => $cities,
            'filters' => $filters,
            'sort' => $sort,
            'chips' => $this->activeChips($request, $filters, $category, $subcategory, $producers, $cities),
        ]);
    }

    public function producers(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $sort = $request->query('sort', 'rating');

        $producers = Producer::approved()
            ->with('city')
            ->withCount(['products' => fn ($query) => $query->visible()])
            ->when($search, function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('description', 'like', $like));
            })
            ->when((int) $request->query('city'), fn ($query, $cityId) => $query->where('city_id', $cityId))
            ->when(ProducerType::tryFrom((string) $request->query('type')), fn ($query, $type) => $query->where('type', $type))
            ->when($sort === 'new', fn ($query) => $query->latest('approved_at'))
            ->when($sort === 'products', fn ($query) => $query->orderByDesc('products_count'))
            ->when(! in_array($sort, ['new', 'products'], true), fn ($query) => $query->orderByDesc('rating')->orderByDesc('reviews_count'))
            ->paginate(12)
            ->withQueryString();

        return view('catalog.producers', [
            'producers' => $producers,
            'cities' => City::active()->get(),
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    /** Список применённых фильтров («чипсы») со ссылками для их сброса. */
    private function activeChips(Request $request, array $filters, ?Category $category, ?Category $subcategory, $producers, $cities): array
    {
        $chips = [];
        $remove = fn (array $keys) => $request->fullUrlWithoutQuery([...$keys, 'page']);

        if ($filters['q']) {
            $chips[] = ['label' => '«'.$filters['q'].'»', 'url' => $remove(['q'])];
        }
        if ($category) {
            $chips[] = ['label' => $category->name, 'url' => $remove(['category', 'subcategory'])];
        }
        if ($subcategory) {
            $chips[] = ['label' => $subcategory->name, 'url' => $remove(['subcategory'])];
        }
        if ($filters['price_min'] !== null) {
            $chips[] = ['label' => 'от '.money($filters['price_min']), 'url' => $remove(['price_min'])];
        }
        if ($filters['price_max'] !== null) {
            $chips[] = ['label' => 'до '.money($filters['price_max']), 'url' => $remove(['price_max'])];
        }
        if ($filters['producer_id'] && $producer = $producers->firstWhere('id', $filters['producer_id'])) {
            $chips[] = ['label' => $producer->name, 'url' => $remove(['producer'])];
        }
        if ($filters['city_id'] && $city = $cities->firstWhere('id', $filters['city_id'])) {
            $chips[] = ['label' => $city->name, 'url' => $remove(['city'])];
        }
        if ($filters['rating']) {
            $chips[] = ['label' => 'Рейтинг от '.str_replace('.', ',', (string) $filters['rating']), 'url' => $remove(['rating'])];
        }
        if ($filters['in_stock']) {
            $chips[] = ['label' => 'В наличии', 'url' => $remove(['in_stock'])];
        }

        return $chips;
    }
}
