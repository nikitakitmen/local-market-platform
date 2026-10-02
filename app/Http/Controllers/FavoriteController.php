<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $products = $request->user()->favorites()
            ->with(['producer', 'variants'])
            ->orderByPivot('created_at', 'desc')
            ->paginate(12);

        return view('account.favorites', ['products' => $products]);
    }

    /** Добавить товар в избранное или убрать из него. */
    public function toggle(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $result = $user->favorites()->toggle($product->id);
        $favorited = ! empty($result['attached']);
        $message = $favorited ? 'Товар добавлен в избранное.' : 'Товар удалён из избранного.';

        if ($request->expectsJson()) {
            return response()->json([
                'favorited' => $favorited,
                'count' => $user->favorites()->count(),
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
