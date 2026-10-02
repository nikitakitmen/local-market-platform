<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Проверка номера телефона: 10–15 цифр, допускаются +, пробелы, скобки и дефисы.
 * Примеры: +7 (900) 123-45-67, 89001234567.
 */
class Phone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (string) $value;
        $digits = preg_replace('/\D/', '', $value);

        if (! preg_match('/^\+?[\d\s\-()]+$/', $value) || strlen($digits) < 10 || strlen($digits) > 15) {
            $fail('Укажите корректный номер телефона, например +7 (900) 123-45-67.');
        }
    }
}
