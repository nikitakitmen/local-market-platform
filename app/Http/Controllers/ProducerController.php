<?php

namespace App\Http\Controllers;

use App\Models\Producer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Публичная страница производителя.
 */
class ProducerController extends Controller
{
    public function show(Request $request, Producer $producer): View
    {
        $user = $request->user();
        abort_unless($producer->isApproved() || $user?->producer?->id === $producer->id || $user?->isStaff(), 404);

        $producer->load(['city', 'locations']);
        $sort = array_key_exists((string) $request->query('sort'), Product::SORTS) ? $request->query('sort') : 'popular';

        $products = $producer->products()
            ->visible()
            ->with(['producer', 'variants'])
            ->sort($sort)
            ->paginate(12)
            ->withQueryString()
            ->fragment('products');

        $reviews = $producer->reviews()->visible()->with(['user', 'product'])->latest()->limit(6)->get();

        return view('producers.show', [
            'producer' => $producer,
            'products' => $products,
            'reviews' => $reviews,
            'sort' => $sort,
            'completedOrders' => $producer->orders()->completed()->count(),
        ]);
    }
}
