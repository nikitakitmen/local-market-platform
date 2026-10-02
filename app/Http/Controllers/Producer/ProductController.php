<?php

namespace App\Http\Controllers\Producer;

use App\Http\Requests\Producer\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Товары производителя: создание, редактирование, удаление, скрытие и наличие.
 * Права проверяются политикой ProductPolicy (только владелец, публикация — после подтверждения).
 */
class ProductController extends BaseController
{
    public function index(Request $request): View
    {
        $producer = $this->producer($request);
        $search = trim((string) $request->query('q'));
        $filter = $request->query('filter');

        $products = $producer->products()
            ->with(['category', 'subcategory', 'variants'])
            ->when($search, fn ($q) => $q->where('name', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->when($filter === 'active', fn ($q) => $q->where('is_active', true))
            ->when($filter === 'hidden', fn ($q) => $q->where('is_active', false))
            ->when($filter === 'out', fn ($q) => $q->where('in_stock', false))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('producer.products.index', compact('producer', 'products', 'search', 'filter'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Product::class);

        return view('producer.products.form', [
            'product' => new Product(['is_active' => true, 'in_stock' => true]),
            'categories' => $this->categories(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);
        $producer = $this->producer($request);
        $data = $request->validated();

        $product = DB::transaction(function () use ($producer, $data, $request) {
            $product = $producer->products()->create($this->attributes($data));
            $this->saveImages($product, $request);
            $this->syncVariants($product, $data['variants'] ?? []);

            return $product;
        });

        return redirect()->route('producer.products.index')->with('success', 'Товар «'.$product->name.'» добавлен.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('producer.products.form', [
            'product' => $product->load(['variants', 'images']),
            'categories' => $this->categories(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);
        $data = $request->validated();

        DB::transaction(function () use ($product, $data, $request) {
            $product->update($this->attributes($data));
            $this->saveImages($product, $request);
            $this->syncVariants($product, $data['variants'] ?? []);
        });

        return redirect()->route('producer.products.index')->with('success', 'Изменения в товаре «'.$product->name.'» сохранены.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        // Мягкое удаление: товар исчезает из каталога, но старые заказы и отзывы сохраняются
        $product->delete();

        return redirect()->route('producer.products.index')->with('success', 'Товар «'.$product->name.'» удалён.');
    }

    /** Временно скрыть товар или снова показать покупателям. */
    public function toggleActive(Product $product): RedirectResponse
    {
        $this->authorize('update', $product);
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('success', $product->is_active ? 'Товар снова виден покупателям.' : 'Товар скрыт из каталога.');
    }

    /** Есть в наличии / нет в наличии. */
    public function toggleStock(Product $product): RedirectResponse
    {
        $this->authorize('update', $product);
        $product->update(['in_stock' => ! $product->in_stock]);

        return back()->with('success', $product->in_stock ? 'Товар отмечен как «в наличии».' : 'Товар отмечен как «нет в наличии».');
    }

    private function attributes(array $data): array
    {
        $attributes = Arr::only($data, ['name', 'category_id', 'subcategory_id', 'description', 'unit', 'in_stock', 'is_active']);

        // При наличии вариантов цена товара будет пересчитана как минимальная цена варианта
        if (isset($data['price'])) {
            $attributes['price'] = $data['price'];
        } elseif (! empty($data['variants'])) {
            $attributes['price'] = min(array_column($data['variants'], 'price'));
        }

        return $attributes;
    }

    /** Основное фото, дополнительные фото и удаление отмеченных фото. */
    private function saveImages(Product $product, Request $request): void
    {
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $product->update(['image' => $request->file('image')->store('products', 'public')]);
        }

        if ($ids = $request->input('delete_images')) {
            $product->images()->whereIn('id', $ids)->get()->each(function ($image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            });
        }

        $sort = (int) $product->images()->max('sort_order');

        foreach ($request->file('gallery', []) as $file) {
            $product->images()->create([
                'path' => $file->store('products', 'public'),
                'sort_order' => ++$sort,
            ]);
        }
    }

    /** Синхронизация вариантов: обновляем существующие, добавляем новые, удаляем убранные из формы. */
    private function syncVariants(Product $product, array $variants): void
    {
        $keepIds = [];

        foreach (array_values($variants) as $index => $row) {
            $variant = isset($row['id']) ? $product->variants()->find($row['id']) : null;
            $variant ??= $product->variants()->make();

            $variant->fill([
                'name' => $row['name'],
                'price' => $row['price'],
                'in_stock' => (bool) ($row['in_stock'] ?? false),
                'sort_order' => $index,
            ])->save();

            $keepIds[] = $variant->id;
        }

        $product->variants()->whereNotIn('id', $keepIds)->delete();

        if ($keepIds) {
            $product->syncPriceFromVariants();
        }
    }

    private function categories()
    {
        return Category::roots()->active()->ordered()->with(['children' => fn ($q) => $q->active()])->get();
    }
}
