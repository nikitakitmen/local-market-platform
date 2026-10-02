<?php

namespace App\Http\Requests;

use App\Enums\ProducerType;
use App\Rules\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Анкета производителя: используется в заявке «Стать производителем» и в настройках кабинета.
 */
class ProducerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'type' => ['required', Rule::enum(ProducerType::class)],
            'inn' => ['nullable', 'digits_between:10,12'],
            'city_id' => ['required', 'integer', Rule::exists('cities', 'id')->where('is_active', true)],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32', new Phone],
            'email' => ['required', 'email', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'message' => ['nullable', 'string', 'max:2000'],
            // Безопасная загрузка: только изображения JPG/PNG/WEBP до 2 МБ
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000'],
        ];
    }
}
