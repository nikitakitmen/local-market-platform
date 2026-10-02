<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Автоматически заполняет поле slug из названия при создании записи.
 * «Сыр Гауда» → «syr-gauda», при совпадении добавляется номер: «syr-gauda-2».
 */
trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = static::uniqueSlug($model->name);
            }
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'item';
        $slug = $base;
        $counter = 2;

        $query = method_exists(static::class, 'bootSoftDeletes')
            ? static::withTrashed()
            : static::query();

        while ((clone $query)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
