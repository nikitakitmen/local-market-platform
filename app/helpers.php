<?php

/*
 * Небольшие глобальные функции для шаблонов.
 * Подключаются через composer.json → autoload.files.
 */

if (! function_exists('money')) {
    /**
     * Форматирование суммы в рублях: 1250 → «1 250 ₽», 99.5 → «99,50 ₽».
     */
    function money(float|int|string|null $amount): string
    {
        $amount = (float) $amount;
        $decimals = floor($amount) == $amount ? 0 : 2;

        return number_format($amount, $decimals, ',', "\u{00A0}")."\u{00A0}₽";
    }
}

if (! function_exists('plural')) {
    /**
     * Склонение слова после числа: plural(5, 'товар', 'товара', 'товаров') → «товаров».
     */
    function plural(int $number, string $one, string $few, string $many): string
    {
        $n = abs($number) % 100;
        $n1 = $n % 10;

        if ($n > 10 && $n < 20) {
            return $many;
        }

        if ($n1 > 1 && $n1 < 5) {
            return $few;
        }

        return $n1 === 1 ? $one : $many;
    }
}

if (! function_exists('favorite_ids')) {
    /**
     * ID товаров в избранном текущего пользователя.
     * Запрос к БД выполняется один раз за HTTP-запрос, а не для каждой карточки товара.
     */
    function favorite_ids(): array
    {
        $request = request();

        if (! $request->attributes->has('favorite_ids')) {
            $request->attributes->set('favorite_ids', auth()->user()?->favorites()->pluck('products.id')->all() ?? []);
        }

        return $request->attributes->get('favorite_ids');
    }
}
