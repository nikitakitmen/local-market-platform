<?php

namespace App\Http\Requests\Producer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Создание и редактирование товара производителем.
 * Права (владелец товара, подтверждённый производитель) проверяются политикой ProductPolicy.
 */
class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Убираем пустые строки вариантов, которые остались в форме. */
    protected function prepareForValidation(): void
    {
        $variants = collect($this->input('variants', []))
            ->filter(fn ($variant) => filled($variant['name'] ?? null) || filled($variant['price'] ?? null))
            ->values()
            ->all();

        $this->merge([
            'variants' => $variants,
            'in_stock' => $this->boolean('in_stock'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $image = ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000'];

        return [
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->whereNull('parent_id')],
            // Подкатегория должна принадлежать выбранной категории
            'subcategory_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('parent_id', (int) $this->input('category_id'))],
            'description' => ['nullable', 'string', 'max:5000'],
            // Если у товара есть варианты, цена берётся из них
            'price' => ['nullable', 'required_without:variants', 'numeric', 'min:1', 'max:1000000'],
            'unit' => ['nullable', 'string', 'max:30'],
            'in_stock' => ['boolean'],
            'is_active' => ['boolean'],

            'image' => ['nullable', ...$image],
            'gallery' => ['nullable', 'array', 'max:6'],
            'gallery.*' => $image,
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer'],

            'variants' => ['array', 'max:10'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.name' => ['required', 'string', 'max:100'],
            'variants.*.price' => ['required', 'numeric', 'min:1', 'max:1000000'],
            'variants.*.in_stock' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'категория',
            'subcategory_id' => 'подкатегория',
            'gallery.*' => 'дополнительное фото',
            'variants.*.name' => 'название варианта',
            'variants.*.price' => 'цена варианта',
        ];
    }

    public function messages(): array
    {
        return [
            'price.required_without' => 'Укажите цену или добавьте варианты товара.',
            'subcategory_id.exists' => 'Подкатегория не относится к выбранной категории.',
        ];
    }
}
