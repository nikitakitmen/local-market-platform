<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Категории: Категория → Подкатегория. Платформа подходит не только для продуктов питания.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            ['Продукты', 'basket', 'Фермерские продукты: молочное, мясо, мёд, овощи.', [
                'Молочные продукты', 'Мясо, птица и яйца', 'Мёд и сладости', 'Овощи и зелень',
            ]],
            ['Выпечка', 'cake2', 'Хлеб на закваске, круассаны и домашние пироги.', [
                'Хлеб', 'Сладкая выпечка', 'Пироги',
            ]],
            ['Напитки', 'cup-hot', 'Свежеобжаренный кофе, травяной чай, лимонады.', [
                'Кофе', 'Чай', 'Соки и лимонады',
            ]],
            ['Цветы и растения', 'flower1', 'Авторские букеты и комнатные растения.', [
                'Букеты', 'Комнатные растения',
            ]],
            ['Ручная работа', 'palette', 'Керамика, свечи, текстиль и декор от мастерских.', [
                'Керамика', 'Свечи и декор', 'Аксессуары',
            ]],
            ['Косметика', 'droplet', 'Натуральное мыло и уходовая косметика локальных брендов.', [
                'Мыло', 'Уход за телом',
            ]],
        ];

        foreach ($tree as $index => [$name, $icon, $description, $children]) {
            $parent = Category::create([
                'name' => $name,
                'icon' => $icon,
                'description' => $description,
                'sort_order' => $index,
            ]);

            foreach ($children as $childIndex => $childName) {
                Category::create([
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'sort_order' => $childIndex,
                ]);
            }
        }
    }
}
