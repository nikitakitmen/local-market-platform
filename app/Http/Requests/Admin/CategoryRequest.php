<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:100'],
            // Родителем может быть только корневая категория (два уровня: категория → подкатегория)
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->whereNull('parent_id'),
                Rule::notIn([$category?->id]),
            ],
            'icon' => ['nullable', 'string', 'max:50', 'regex:/^[a-z0-9-]+$/'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $category = $this->route('category');

                if ($category && $this->filled('parent_id') && $category->children()->exists()) {
                    $validator->errors()->add('parent_id', 'У категории есть подкатегории — её нельзя сделать подкатегорией.');
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'parent_id' => 'родительская категория',
            'icon' => 'иконка',
        ];
    }
}
