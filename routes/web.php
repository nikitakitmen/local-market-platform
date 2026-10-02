<?php

use App\Http\Controllers\Account;
use App\Http\Controllers\Admin;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Courier\DeliveryController as CourierDeliveryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Producer;
use App\Http\Controllers\ProducerApplicationController;
use App\Http\Controllers\ProducerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Публичная часть сайта
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/catalog', [CatalogController::class, 'products'])->name('catalog');
Route::get('/catalog/producers', [CatalogController::class, 'producers'])->name('catalog.producers');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/producers/{producer:slug}', [ProducerController::class, 'show'])->name('producers.show');

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Общие разделы для всех авторизованных пользователей
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/notifications/{id}/open', [NotificationController::class, 'open'])->name('notifications.open');

    // Чат: отправка сообщения и AJAX-обновление (общие для покупателя и производителя)
    Route::post('/messages/{conversation}', [ChatController::class, 'store'])->name('messages.store');
    Route::get('/messages/{conversation}/poll', [ChatController::class, 'poll'])->name('messages.poll');

    Route::post('/reviews/{review}/report', [ReviewController::class, 'report'])->name('reviews.report');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

/*
|--------------------------------------------------------------------------
| Покупатель (производитель тоже может делать покупки)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:buyer,producer'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/items/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::post('/cart/promo', [CartController::class, 'applyPromo'])->name('cart.promo.apply');
    Route::delete('/cart/promo', [CartController::class, 'removePromo'])->name('cart.promo.remove');

    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/{checkoutId}/success', [CheckoutController::class, 'success'])->name('checkout.success');

    // Демонстрационная оплата картой / СБП (реальные платёжные системы не подключены)
    Route::get('/payment/{checkoutId}', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('/payment/{checkoutId}', [PaymentController::class, 'pay'])->name('payment.pay');

    Route::post('/favorites/{product}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/producers/{producer}/chat', [ChatController::class, 'start'])->name('chat.start');

    Route::get('/become-producer', [ProducerApplicationController::class, 'create'])->name('producer-application.create');
    Route::post('/become-producer', [ProducerApplicationController::class, 'store'])->name('producer-application.store');

    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [Account\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/orders', [Account\OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [Account\OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/cancel', [Account\OrderController::class, 'cancel'])->name('orders.cancel');
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites');
        Route::get('/reviews', [Account\ReviewController::class, 'index'])->name('reviews');
        Route::resource('addresses', Account\AddressController::class)->except('show');
        Route::get('/messages/{conversation?}', [ChatController::class, 'index'])->name('messages');
    });
});

/*
|--------------------------------------------------------------------------
| Кабинет производителя
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:producer'])->prefix('producer')->name('producer.')->group(function () {
    Route::get('/', [Producer\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('products', Producer\ProductController::class)->except('show');
    Route::patch('products/{product}/toggle-active', [Producer\ProductController::class, 'toggleActive'])->name('products.toggle-active');
    Route::patch('products/{product}/toggle-stock', [Producer\ProductController::class, 'toggleStock'])->name('products.toggle-stock');

    Route::get('orders', [Producer\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [Producer\OrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/accept', [Producer\OrderController::class, 'accept'])->name('orders.accept');
    Route::post('orders/{order}/complete', [Producer\OrderController::class, 'complete'])->name('orders.complete');
    Route::post('orders/{order}/cancel', [Producer\OrderController::class, 'cancel'])->name('orders.cancel');

    Route::resource('locations', Producer\LocationController::class)->except('show');

    Route::get('reviews', [Producer\ReviewController::class, 'index'])->name('reviews.index');
    Route::post('reviews/{review}/reply', [Producer\ReviewController::class, 'reply'])->name('reviews.reply');

    Route::get('messages/{conversation?}', [ChatController::class, 'index'])->name('messages');
    Route::get('analytics', [Producer\AnalyticsController::class, 'index'])->name('analytics');
    Route::get('settings', [Producer\SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [Producer\SettingsController::class, 'update'])->name('settings.update');
});

/*
|--------------------------------------------------------------------------
| Кабинет курьера
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:courier'])->prefix('courier')->name('courier.')->group(function () {
    Route::get('/', [CourierDeliveryController::class, 'available'])->name('available');
    Route::get('/my', [CourierDeliveryController::class, 'my'])->name('my');
    Route::get('/completed', [CourierDeliveryController::class, 'completed'])->name('completed');
    Route::post('/deliveries/{delivery}/take', [CourierDeliveryController::class, 'take'])->name('take');
    Route::post('/deliveries/{delivery}/pickup', [CourierDeliveryController::class, 'pickup'])->name('pickup');
    Route::post('/deliveries/{delivery}/deliver', [CourierDeliveryController::class, 'deliver'])->name('deliver');
});

/*
|--------------------------------------------------------------------------
| Панель управления: администратор и оператор
|--------------------------------------------------------------------------
| Оператор видит ограниченный набор разделов (заказы, доставки, производители, жалобы).
| Критические разделы (пользователи, категории, промокоды, настройки) — только для администратора.
*/

Route::middleware(['auth', 'role:admin,operator'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('deliveries', [Admin\DeliveryController::class, 'index'])->name('deliveries.index');
    Route::patch('deliveries/{delivery}/assign', [Admin\DeliveryController::class, 'assign'])->name('deliveries.assign');
    Route::patch('deliveries/{delivery}/unassign', [Admin\DeliveryController::class, 'unassign'])->name('deliveries.unassign');

    Route::get('producers', [Admin\ProducerController::class, 'index'])->name('producers.index');
    Route::get('producers/{producer}', [Admin\ProducerController::class, 'show'])->name('producers.show');

    Route::get('reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::patch('reports/{report}', [Admin\ReportController::class, 'update'])->name('reports.update');
    Route::patch('reviews/{review}/visibility', [Admin\ReviewController::class, 'toggleVisibility'])->name('reviews.visibility');

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', Admin\UserController::class)->except(['show', 'destroy']);
        Route::patch('users/{user}/toggle-active', [Admin\UserController::class, 'toggleActive'])->name('users.toggle-active');

        Route::patch('producers/{producer}/status', [Admin\ProducerController::class, 'updateStatus'])->name('producers.status');

        Route::get('applications', [Admin\ApplicationController::class, 'index'])->name('applications.index');
        Route::get('applications/{application}', [Admin\ApplicationController::class, 'show'])->name('applications.show');
        Route::post('applications/{application}/approve', [Admin\ApplicationController::class, 'approve'])->name('applications.approve');
        Route::post('applications/{application}/reject', [Admin\ApplicationController::class, 'reject'])->name('applications.reject');

        Route::get('products', [Admin\ProductController::class, 'index'])->name('products.index');
        Route::patch('products/{product}/toggle', [Admin\ProductController::class, 'toggle'])->name('products.toggle');
        Route::delete('products/{product}', [Admin\ProductController::class, 'destroy'])->name('products.destroy');

        Route::resource('categories', Admin\CategoryController::class)->except('show');

        Route::get('reviews', [Admin\ReviewController::class, 'index'])->name('reviews.index');
        Route::delete('reviews/{review}', [Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

        Route::resource('promo-codes', Admin\PromoCodeController::class)->except('show')
            ->parameters(['promo-codes' => 'promoCode']);

        Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
    });
});
