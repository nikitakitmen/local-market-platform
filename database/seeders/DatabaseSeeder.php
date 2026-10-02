<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Notifications\SiteNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Демо-данные платформы. Запуск: php artisan migrate --seed (или migrate:fresh --seed).
 * Пароль всех демо-аккаунтов: password.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Очищаем ранее сгенерированные демо-изображения
        Storage::disk('public')->deleteDirectory('products');
        Storage::disk('public')->deleteDirectory('producers');

        $this->call([
            SettingSeeder::class,
            CitySeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
            ProducerSeeder::class,
            PromoCodeSeeder::class,
            OrderSeeder::class,
            ReviewSeeder::class,
            DemoActivitySeeder::class,
        ]);

        $this->notifyAboutCurrentOrders();
    }

    /** Уведомления о текущих заказах — чтобы колокольчик не был пустым. */
    private function notifyAboutCurrentOrders(): void
    {
        $orders = Order::whereIn('status', [OrderStatus::New, OrderStatus::Accepted])->with(['buyer', 'producer.user'])->get();

        foreach ($orders as $order) {
            if ($order->status === OrderStatus::New) {
                $order->producer->user->notify(new SiteNotification(
                    'Новый заказ '.$order->number,
                    'Покупатель '.$order->buyer->name.' оформил заказ на сумму '.money($order->total).'.',
                    route('producer.orders.show', $order),
                    'bi-bag-plus'
                ));
            } else {
                $order->buyer->notify(new SiteNotification(
                    'Заказ '.$order->number.' принят',
                    'Производитель «'.$order->producer->name.'» принял ваш заказ.',
                    route('account.orders.show', $order),
                    'bi-check2-circle'
                ));
            }
        }
    }
}
