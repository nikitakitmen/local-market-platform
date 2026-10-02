<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Request $request, Product $product, ReviewService $reviewService): View
    {
        $product->load(['producer.city', 'category', 'subcategory', 'variants', 'images']);
        $user = $request->user();

        // Скрытый товар видят только его владелец и сотрудники платформы
        $isOwner = $user && $user->producer?->id === $product->producer_id;
        abort_unless(
            ($product->is_active && $product->producer->isApproved()) || $isOwner || $user?->isStaff(),
            404
        );

        $reviews = $product->reviews()->visible()->with('user')->latest()->paginate(5)->fragment('reviews');

        $ratingCounts = $product->reviews()->visible()
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        // Похожие товары: та же категория, сначала из той же подкатегории
        $similar = Product::visible()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->with(['producer', 'variants'])
            ->orderByRaw('CASE WHEN subcategory_id = ? THEN 0 ELSE 1 END', [(int) $product->subcategory_id])
            ->sort('popular')
            ->limit(4)
            ->get();

        return view('products.show', [
            'product' => $product,
            'reviews' => $reviews,
            'ratingCounts' => $ratingCounts,
            'similar' => $similar,
            'canReview' => $user && $user->canShop() && $reviewService->canReview($user, $product),
            'userReview' => $user ? Review::where('user_id', $user->id)->where('product_id', $product->id)->first() : null,
            'reportedIds' => $user ? ReviewReport::where('user_id', $user->id)->pluck('review_id')->all() : [],
            'isOwner' => $isOwner,
        ]);
    }
}
