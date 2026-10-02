<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Корзина покупателя. Добавление и изменение количества работают через AJAX,
 * но формы продолжают работать и без JavaScript (обычный POST с редиректом).
 */
class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(Request $request): View
    {
        return view('cart.index', ['summary' => $this->cart->summary($request->user())]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:'.CartService::MAX_QUANTITY],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $item = $this->cart->add($request->user(), $product, $data['variant_id'] ?? null, $data['quantity'] ?? 1);

        $message = 'Добавлено в корзину: '.$product->name.($item->variant ? ' ('.$item->variant->name.')' : '');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'count' => $this->cart->count($request->user()),
            ]);
        }

        return back()->with('success', $message);
    }

    public function update(Request $request, CartItem $item): JsonResponse|RedirectResponse
    {
        $this->ensureOwner($request, $item);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:'.CartService::MAX_QUANTITY],
        ]);

        $this->cart->updateQuantity($item, $data['quantity']);

        return $this->respond($request);
    }

    public function destroy(Request $request, CartItem $item): JsonResponse|RedirectResponse
    {
        $this->ensureOwner($request, $item);
        $this->cart->remove($item);

        return $this->respond($request, 'Товар удалён из корзины.');
    }

    public function applyPromo(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:50']]);
        $promo = $this->cart->applyPromo($request->user(), $data['code']);

        return back()->with('success', 'Промокод '.$promo->code.' применён.');
    }

    public function removePromo(Request $request): RedirectResponse
    {
        $this->cart->removePromo($request->user());

        return back()->with('success', 'Промокод удалён.');
    }

    /** Проверка владельца: изменять можно только позиции своей корзины. */
    private function ensureOwner(Request $request, CartItem $item): void
    {
        abort_unless($item->cart->user_id === $request->user()->id, 403);
    }

    /** Для AJAX возвращаем заново отрисованное содержимое корзины. */
    private function respond(Request $request, ?string $message = null): JsonResponse|RedirectResponse
    {
        if (! $request->expectsJson()) {
            return back()->with('success', $message ?? 'Корзина обновлена.');
        }

        $summary = $this->cart->summary($request->user());

        return response()->json([
            'message' => $message,
            'count' => $summary['count'],
            'html' => view('cart.partials.content', ['summary' => $summary])->render(),
        ]);
    }
}
