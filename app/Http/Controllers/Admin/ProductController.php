<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Producer;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Модерация товаров администратором.
 */
class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $products = Product::query()
            ->with(['producer', 'category', 'variants'])
            ->when($search, fn ($q) => $q->where('name', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->when((int) $request->query('producer'), fn ($q, $id) => $q->where('producer_id', $id))
            ->when((int) $request->query('category'), fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->query('visibility') === 'hidden', fn ($q) => $q->where('is_active', false))
            ->when($request->query('visibility') === 'active', fn ($q) => $q->where('is_active', true))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'search' => $search,
            'producers' => Producer::orderBy('name')->get(['id', 'name']),
            'categories' => Category::roots()->ordered()->get(['id', 'name']),
        ]);
    }

    public function toggle(Product $product): RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('success', 'Товар «'.$product->name.'» '.($product->is_active ? 'опубликован.' : 'скрыт из каталога.'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return back()->with('success', 'Товар «'.$product->name.'» удалён.');
    }
}
