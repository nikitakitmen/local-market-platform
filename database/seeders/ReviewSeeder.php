<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Models\Order;
use App\Models\Producer;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Отзывы на товары из выполненных заказов, ответы производителей и жалобы.
 */
class ReviewSeeder extends Seeder
{
    private const TEXTS = [
        5 => [
            'Очень вкусно и свежо! Заказываю уже не первый раз, качество стабильно отличное.',
            'Превзошло ожидания. Курьер приехал вовремя, всё аккуратно упаковано.',
            'Настоящий вкус, как в детстве. Спасибо производителю за любовь к делу!',
            'Отличный товар, буду брать ещё. Рекомендую всем, кто ценит натуральное.',
            'Супер! Брала в подарок — все были в восторге.',
        ],
        4 => [
            'Хороший товар, но хотелось бы упаковку поудобнее.',
            'Всё понравилось, немного задержалась доставка, но предупредили заранее.',
            'Качество на высоте, цена чуть выше, чем в магазине, но оно того стоит.',
        ],
        3 => [
            'Неплохо, но ожидала большего. Возможно, попробую другой вариант.',
        ],
    ];

    private const REPLIES = [
        'Спасибо за тёплый отзыв! Будем рады видеть вас снова.',
        'Благодарим за обратную связь — учтём ваши пожелания!',
    ];

    public function run(): void
    {
        mt_srand(77);

        $mainBuyer = User::where('email', 'buyer@localmarket.test')->first();
        // Последний выполненный заказ главного покупателя оставляем без отзыва — чтобы показать форму отзыва
        $keepForDemo = Order::completed()->where('user_id', $mainBuyer->id)->latest('completed_at')->first();

        $orders = Order::where('status', OrderStatus::Completed)->with('items')->orderBy('completed_at')->get();

        foreach ($orders as $order) {
            if ($keepForDemo && $order->id === $keepForDemo->id) {
                continue;
            }

            foreach ($order->items as $item) {
                if (! $item->product_id || mt_rand(1, 100) > 65) {
                    continue;
                }

                if (Review::where('user_id', $order->user_id)->where('product_id', $item->product_id)->exists()) {
                    continue;
                }

                $rating = [5, 5, 5, 4, 4, 5, 3][mt_rand(0, 6)];
                $texts = self::TEXTS[$rating];

                $review = Review::forceCreate([
                    'user_id' => $order->user_id,
                    'product_id' => $item->product_id,
                    'producer_id' => $order->producer_id,
                    'order_id' => $order->id,
                    'rating' => $rating,
                    'text' => $texts[mt_rand(0, count($texts) - 1)],
                    'created_at' => $order->completed_at->copy()->addHours(mt_rand(2, 30)),
                    'updated_at' => $order->completed_at->copy()->addHours(mt_rand(2, 30)),
                ]);

                // Производитель отвечает примерно на каждый третий отзыв
                if (mt_rand(1, 3) === 1) {
                    $review->forceFill([
                        'reply' => self::REPLIES[mt_rand(0, 1)],
                        'replied_at' => $review->created_at->copy()->addHours(3),
                    ])->save();
                }
            }
        }

        $this->createReports();

        Product::withTrashed()->each(fn (Product $product) => $product->recalculateRating());
        Producer::all()->each(fn (Producer $producer) => $producer->recalculateRating());
    }

    private function createReports(): void
    {
        $maria = User::where('email', 'maria@localmarket.test')->first();
        $dmitry = User::where('email', 'dmitry@localmarket.test')->first();

        $reviews = Review::where('user_id', '!=', $maria->id)->orderBy('id')->limit(2)->get();

        if ($reviews->count() < 2) {
            return;
        }

        // Новая жалоба — её видят оператор и администратор
        ReviewReport::create([
            'review_id' => $reviews[0]->id,
            'user_id' => $maria->id,
            'reason' => ReportReason::Fake,
            'comment' => 'Похоже на накрученный отзыв, текст повторяется у разных товаров.',
            'status' => ReportStatus::Pending,
        ]);

        // Уже рассмотренная жалоба
        ReviewReport::forceCreate([
            'review_id' => $reviews[1]->id,
            'user_id' => $dmitry->id === $reviews[1]->user_id ? $maria->id : $dmitry->id,
            'reason' => ReportReason::Spam,
            'comment' => null,
            'status' => ReportStatus::Dismissed,
            'handled_by' => User::where('email', 'operator@localmarket.test')->value('id'),
            'handled_at' => now()->subDays(2),
        ]);
    }
}
