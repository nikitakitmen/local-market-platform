{{-- Состав заказа и итоговые суммы (используется покупателем, производителем и в админке) --}}
<div class="d-grid gap-3">
    @foreach ($order->items as $item)
        <div class="d-flex align-items-center gap-3">
            <img src="{{ $item->product?->image_url ?? asset('images/placeholder-product.svg') }}" alt="" class="table-thumb" loading="lazy">
            <div class="flex-grow-1 min-w-0">
                @if ($item->product && ! $item->product->trashed() && ($linkProducts ?? true))
                    <a href="{{ route('products.show', $item->product->slug) }}" class="fw-semibold text-reset d-block text-truncate">{{ $item->product_name }}</a>
                @else
                    <span class="fw-semibold d-block text-truncate">{{ $item->product_name }}</span>
                @endif
                <span class="small text-secondary">{{ $item->variant_name ? $item->variant_name.' · ' : '' }}{{ money($item->price) }} × {{ $item->quantity }}</span>
            </div>
            <div class="fw-bold text-nowrap">{{ money($item->total) }}</div>
        </div>
    @endforeach
</div>

<div class="mt-3 pt-3 border-top">
    <div class="key-value"><span>Товары</span><span>{{ money($order->subtotal) }}</span></div>
    @if ($order->discount > 0)
        <div class="key-value"><span>Скидка{{ $order->promoCode ? ' ('.$order->promoCode->code.')' : '' }}</span><span class="text-primary">−{{ money($order->discount) }}</span></div>
    @endif
    @if ($order->isDelivery())
        <div class="key-value"><span>Доставка{{ $order->delivery_distance_km ? ' ('.$order->delivery_distance_km.' км)' : '' }}</span><span>{{ $order->delivery_cost > 0 ? money($order->delivery_cost) : 'Бесплатно' }}</span></div>
    @endif
    <div class="key-value fs-5"><span class="fw-bold text-body">Итого</span><span class="fw-bold">{{ money($order->total) }}</span></div>
</div>
