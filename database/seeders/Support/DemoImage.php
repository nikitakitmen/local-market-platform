<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Генератор демонстрационных изображений (SVG) для seed-данных.
 * Вместо реальных фотографий — мягкий цветной фон и эмодзи товара.
 * Файлы сохраняются в Laravel Storage (диск public), как и загруженные пользователями фото.
 */
class DemoImage
{
    /** Пастельные фоны для карточек товаров. */
    private const BACKGROUNDS = [
        'cream' => ['#f6efe1', '#e9dcc0'],
        'green' => ['#e7f1e8', '#cfe3d2'],
        'peach' => ['#fbe9df', '#f3cdb8'],
        'blue' => ['#e6eef6', '#c9daea'],
        'pink' => ['#f8e7ec', '#efc9d4'],
        'sand' => ['#f3ece4', '#e1d2c1'],
        'mint' => ['#e3f3ee', '#c4e5da'],
        'lilac' => ['#eeeaf6', '#d8cfee'],
    ];

    public static function product(string $path, string $emoji, string $background = 'cream', int $variant = 0): string
    {
        [$light, $dark] = self::BACKGROUNDS[$background] ?? self::BACKGROUNDS['cream'];

        // Небольшие отличия для дополнительных фотографий одного товара
        $offsets = [[470, 120, 110, 500], [130, 130, 480, 480], [300, 80, 520, 420]];
        [$c1x, $c1y, $c2x, $c2y] = $offsets[$variant % count($offsets)];
        $size = [230, 200, 260][$variant % 3];
        $rotate = [0, -8, 6][$variant % 3];

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600" viewBox="0 0 600 600">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="{$light}"/>
      <stop offset="1" stop-color="{$dark}"/>
    </linearGradient>
  </defs>
  <rect width="600" height="600" fill="url(#bg)"/>
  <circle cx="{$c1x}" cy="{$c1y}" r="150" fill="#ffffff" opacity=".35"/>
  <circle cx="{$c2x}" cy="{$c2y}" r="190" fill="#ffffff" opacity=".22"/>
  <ellipse cx="300" cy="470" rx="160" ry="24" fill="#3b2f20" opacity=".08"/>
  <text x="300" y="320" text-anchor="middle" dominant-baseline="middle" font-size="{$size}"
        transform="rotate({$rotate} 300 300)"
        font-family="'Apple Color Emoji','Segoe UI Emoji','Noto Color Emoji',sans-serif">{$emoji}</text>
</svg>
SVG;

        Storage::disk('public')->put($path, $svg);

        return $path;
    }

    public static function logo(string $path, string $emoji, string $color): string
    {
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="240" height="240" viewBox="0 0 240 240">
  <rect width="240" height="240" rx="56" fill="{$color}"/>
  <circle cx="190" cy="50" r="70" fill="#ffffff" opacity=".15"/>
  <text x="120" y="128" text-anchor="middle" dominant-baseline="middle" font-size="120"
        font-family="'Apple Color Emoji','Segoe UI Emoji','Noto Color Emoji',sans-serif">{$emoji}</text>
</svg>
SVG;

        Storage::disk('public')->put($path, $svg);

        return $path;
    }
}
