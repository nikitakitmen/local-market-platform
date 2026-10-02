<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Работа с корзиной: добавление товаров, изменение количества, промокод и расчёт итогов.
 * Корзина хранится в БД (таблицы carts и cart_items) и доступна авторизованному покупателю.
 */
class CartService
{
    public const MAX_QUANTITY = 99;

    public function cartFor(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    public function add(User $user, Product $product, ?int $variantId = null, int $quantity = 1): CartItem
    {
        $product->loadMissing('variants', 'producer');

        if ($product->producer->user_id === $user->id) {
            throw ValidationException::withMessages(['product' => 'Нельзя добавить в корзину собственный товар.']);
        }

        if (! $product->is_active || ! $product->producer->isApproved()) {
            throw ValidationException::withMessages(['product' => 'Товар сейчас недоступен для заказа.']);
        }

        if (! $product->in_stock) {
            throw ValidationException::withMessages(['product' => 'Товара нет в наличии.']);
        }

        $variant = null;

        if ($product->hasVariants()) {
            $variant = $variantId
                ? $product->variants->firstWhere('id', $variantId)
                : $product->defaultVariant();

            if (! $variant) {
                throw ValidationException::withMessages(['variant_id' => 'Выберите вариант товара.']);
            }

            if (! $variant->in_stock) {
                throw ValidationException::withMessages(['variant_id' => 'Выбранного варианта нет в наличии.']);
            }
        }

        $cart = $this->cartFor($user);

        $item = $cart->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        if ($item) {
            $item->update(['quantity' => min($item->quantity + $quantity, self::MAX_QUANTITY)]);
        } else {
            $item = $cart->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity' => min($quantity, self::MAX_QUANTITY),
            ]);
        }

        $cart->touch();

        return $item->setRelation('product', $product)->setRelation('variant', $variant);
    }

    public function updateQuantity(CartItem $item, int $quantity): void
    {
        $item->update(['quantity' => max(1, min($quantity, self::MAX_QUANTITY))]);
    }

    public function remove(CartItem $item): void
    {
        $item->delete();
    }

    /** Количество единиц товара в корзине (для значка в шапке). */
    public function count(User $user): int
    {
        return (int) CartItem::query()
            ->whereHas('cart', fn ($q) => $q->where('user_id', $user->id))
            ->sum('quantity');
    }

    /**
     * Содержимое корзины, сгруппированное по производителям, и итоговые суммы.
     * Каждая группа при оформлении станет отдельным заказом.
     */
    public function summary(User $user): array
    {
        $cart = $this->cartFor($user)->load('promoCode');

        $items = $cart->items()
            ->with(['product.producer.city', 'product.producer.locations', 'product.variants', 'variant'])
            ->oldest()
            ->get();

        // Позиции удалённых производителем товаров убираем из корзины автоматически
        $orphans = $items->filter(fn (CartItem $item) => $item->product === null);

        if ($orphans->isNotEmpty()) {
            CartItem::whereIn('id', $orphans->pluck('id'))->delete();
            $items = $items->diff($orphans);
        }

        $minOrder = Setting::number('min_order_amount');

        $groups = $items
            ->groupBy(fn (CartItem $item) => $item->product->producer_id)
            ->map(function ($groupItems) use ($minOrder) {
                $subtotal = round($groupItems->filter->isAvailable()->sum(fn (CartItem $item) => $item->total), 2);

                return [
                    'producer' => $groupItems->first()->product->producer,
                    'items' => $groupItems,
                    'subtotal' => $subtotal,
                    // Сколько не хватает до минимальной суммы заказа
                    'missing' => max(0, round($minOrder - $subtotal, 2)),
                ];
            });

        $subtotal = round($groups->sum('subtotal'), 2);
        $promo = $cart->promoCode;
        $promoError = $promo?->errorFor($subtotal);
        $discount = ($promo && ! $promoError) ? $promo->discountFor($subtotal) : 0.0;
        $hasUnavailable = $items->contains(fn (CartItem $item) => ! $item->isAvailable());

        return [
            'cart' => $cart,
            'groups' => $groups,
            'count' => (int) $items->sum('quantity'),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => round($subtotal - $discount, 2),
            'promo' => $promo,
            'promo_error' => $promoError,
            'min_order' => $minOrder,
            'has_unavailable' => $hasUnavailable,
            'can_checkout' => $items->isNotEmpty()
                && ! $hasUnavailable
                && $groups->every(fn ($group) => $group['missing'] <= 0),
        ];
    }

    public function applyPromo(User $user, string $code): PromoCode
    {
        $promo = PromoCode::where('code', mb_strtoupper(trim($code)))->first();

        if (! $promo) {
            throw ValidationException::withMessages(['code' => 'Промокод не найден.']);
        }

        $summary = $this->summary($user);

        if ($error = $promo->errorFor($summary['subtotal'])) {
            throw ValidationException::withMessages(['code' => $error]);
        }

        $summary['cart']->update(['promo_code_id' => $promo->id]);

        return $promo;
    }

    public function removePromo(User $user): void
    {
        $this->cartFor($user)->update(['promo_code_id' => null]);
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
        $cart->update(['promo_code_id' => null]);
    }
}
