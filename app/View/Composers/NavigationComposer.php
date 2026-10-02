<?php

namespace App\View\Composers;

use App\Models\Category;
use App\Models\Setting;
use App\Services\CartService;
use Illuminate\View\View;

/**
 * Передаёт в шаблоны-раскладки данные для шапки: категории меню,
 * количество товаров в корзине, непрочитанные уведомления и сообщения.
 */
class NavigationComposer
{
    public function __construct(private CartService $cart) {}

    public function compose(View $view): void
    {
        // Раскладок на странице может быть несколько (app → account), поэтому данные
        // вычисляем один раз за HTTP-запрос и храним в атрибутах запроса.
        $request = request();

        if (! $request->attributes->has('nav')) {
            $request->attributes->set('nav', $this->data());
        }

        $view->with('nav', $request->attributes->get('nav'));
    }

    private function data(): array
    {
        $user = auth()->user();

        $data = [
            'siteName' => Setting::get('site_name'),
            'supportPhone' => Setting::get('support_phone'),
            'supportEmail' => Setting::get('support_email'),
            'categories' => Category::roots()->active()->ordered()->with(['children' => fn ($q) => $q->active()])->get(),
            'cart' => 0,
            'favorites' => 0,
            'notifications' => 0,
            'messages' => 0,
            'latestNotifications' => collect(),
        ];

        if (! $user) {
            return $data;
        }

        if ($user->canShop()) {
            $data['cart'] = $this->cart->count($user);
            $data['favorites'] = $user->favorites()->count();
            $data['messages'] = $user->unreadMessagesCount();
        }

        $data['notifications'] = $user->unreadNotifications()->count();
        $data['latestNotifications'] = $user->notifications()->latest()->limit(5)->get();

        return $data;
    }
}
