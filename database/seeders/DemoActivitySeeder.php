<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Conversation;
use App\Models\Producer;
use App\Models\Product;
use App\Models\User;
use App\Notifications\SiteNotification;
use Illuminate\Database\Seeder;

/**
 * Активность демо-пользователей: избранное, корзина, переписка, уведомления.
 */
class DemoActivitySeeder extends Seeder
{
    public function run(): void
    {
        $buyer = User::where('email', 'buyer@localmarket.test')->first();
        $farm = Producer::where('email', 'farm@localmarket.test')->first();
        $coffee = Producer::where('email', 'coffee@localmarket.test')->first();
        $product = fn (string $name) => Product::where('name', $name)->with('variants')->firstOrFail();

        // Избранное
        $buyer->favorites()->attach([
            $product('Сыр «Качотта» выдержанный')->id,
            $product('Кофе «Эфиопия Иргачеффе»')->id,
            $product('Букет «Летнее утро»')->id,
            $product('Свеча соевая «Хвоя»')->id,
        ]);

        // Корзина с товарами двух производителей — при оформлении получится два заказа
        $cart = Cart::create(['user_id' => $buyer->id]);
        $cheese = $product('Сыр «Качотта» выдержанный');
        $croissant = $product('Круассан классический');
        $cart->items()->createMany([
            ['product_id' => $cheese->id, 'product_variant_id' => $cheese->variants->firstWhere('name', '500 г')->id, 'quantity' => 1],
            ['product_id' => $product('Молоко фермерское 3,5–4,5%')->id, 'quantity' => 2],
            ['product_id' => $croissant->id, 'product_variant_id' => $croissant->variants->firstWhere('name', 'Набор 4 шт')->id, 'quantity' => 1],
            ['product_id' => $product('Хлеб на закваске «Деревенский»')->id, 'quantity' => 1],
        ]);

        // Переписка покупателя с производителями
        $chat = Conversation::create(['buyer_id' => $buyer->id, 'producer_id' => $farm->id]);
        $this->message($chat, $buyer, 'Здравствуйте! Подскажите, сыр «Качотта» сейчас есть с выдержкой больше 2 месяцев?', 26 * 60, true);
        $this->message($chat, $farm->user, 'Добрый день! Да, есть партия с выдержкой 3 месяца — вкус более насыщенный. Цена та же.', 25 * 60, true);
        $this->message($chat, $buyer, 'Отлично, тогда закажу 500 г. А молоко привозите каждый день?', 30, true);
        $this->message($chat, $farm->user, 'Молоко отгружаем ежедневно, кроме понедельника. Закажите до 18:00 — привезём на следующий день утром.', 20, false);

        $chat2 = Conversation::create(['buyer_id' => $buyer->id, 'producer_id' => $coffee->id]);
        $this->message($chat2, $buyer, 'Добрый вечер! Можно смолоть «Эфиопию» под гейзерную кофеварку?', 3 * 24 * 60, true);
        $this->message($chat2, $coffee->user, 'Здравствуйте! Конечно, напишите в комментарии к заказу «помол под гейзер».', 3 * 24 * 60 - 15, true);

        // Уведомления
        $buyer->notify(new SiteNotification('Добро пожаловать!', 'Спасибо за регистрацию. Попробуйте промокод WELCOME10 на первый заказ.', route('catalog'), 'bi-gift'));
        $farm->user->notify(new SiteNotification('Новый отзыв: 5 из 5', 'Покупатель оценил ваш товар на «отлично».', route('producer.reviews.index'), 'bi-star'));
    }

    private function message(Conversation $conversation, User $sender, string $body, int $minutesAgo, bool $read): void
    {
        $time = now()->subMinutes($minutesAgo);

        $conversation->messages()->forceCreate([
            'sender_id' => $sender->id,
            'body' => $body,
            'read_at' => $read ? $time->copy()->addMinutes(5) : null,
            'created_at' => $time,
            'updated_at' => $time,
        ]);

        $conversation->update(['last_message_at' => $time]);
    }
}
