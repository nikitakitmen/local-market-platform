<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Настройки сайта «ключ — значение».
 *
 * Использование: Setting::get('min_order_amount'), Setting::set('platform_commission_percent', 12).
 * Значения кэшируются, кэш сбрасывается при изменении.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    private const CACHE_KEY = 'site_settings';

    /** Значения по умолчанию (используются, если настройка ещё не сохранена в БД). */
    public const DEFAULTS = [
        'site_name' => 'Локальный рынок',
        'support_email' => 'support@localmarket.test',
        'support_phone' => '+7 (800) 555-35-35',
        'platform_commission_percent' => 10,
        'min_order_amount' => 500,
        'delivery_base_cost' => 150,
        'delivery_cost_per_km' => 15,
        'delivery_free_from' => 3000,
    ];

    /** Подписи настроек для формы в админке. */
    public const LABELS = [
        'site_name' => 'Название сайта',
        'support_email' => 'Email поддержки',
        'support_phone' => 'Телефон поддержки',
        'platform_commission_percent' => 'Комиссия платформы, %',
        'min_order_amount' => 'Минимальная сумма заказа, ₽',
        'delivery_base_cost' => 'Базовая стоимость доставки, ₽',
        'delivery_cost_per_km' => 'Стоимость за километр, ₽',
        'delivery_free_from' => 'Бесплатная доставка от, ₽',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $values = Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all());

        return $values[$key] ?? $default ?? self::DEFAULTS[$key] ?? null;
    }

    /** Числовая настройка (суммы, проценты). */
    public static function number(string $key): float
    {
        return (float) static::get($key);
    }

    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_KEY);
    }

    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }
}
