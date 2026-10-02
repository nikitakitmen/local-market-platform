<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Категории товаров (категория → подкатегория). Создаёт и редактирует администратор.
 */
class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::roots()->ordered()
            ->withCount('products')
            ->with(['children' => fn ($q) => $q->withCount('subcategoryProducts')])
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.form', [
            'category' => new Category(['is_active' => true, 'parent_id' => request()->integer('parent') ?: null]),
            'parents' => Category::roots()->ordered()->get(),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()->route('admin.categories.index')->with('success', 'Категория добавлена.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', [
            'category' => $category,
            'parents' => Category::roots()->whereKeyNot($category->id)->ordered()->get(),
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')->with('success', 'Категория «'.$category->name.'» сохранена.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $usedBy = Product::withTrashed()
            ->where('category_id', $category->id)
            ->orWhere('subcategory_id', $category->id)
            ->orWhereIn('subcategory_id', $category->children()->pluck('id'))
            ->exists();

        if ($usedBy) {
            return back()->with('error', 'В категории «'.$category->name.'» есть товары — её нельзя удалить. Скройте её или перенесите товары.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Категория «'.$category->name.'» удалена.');
    }
}
