<?php

namespace App\Http\Requests\Admin;

use App\Enums\PromoCodeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromoCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => mb_strtoupper(trim((string) $this->input('code'))),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $isPercent = $this->input('type') === PromoCodeType::Percent->value;

        return [
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('promo_codes', 'code')->ignore($this->route('promoCode'))],
            'type' => ['required', Rule::enum(PromoCodeType::class)],
            'value' => ['required', 'numeric', 'min:1', $isPercent ? 'max:90' : 'max:100000'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Код может содержать только латинские буквы, цифры, дефис и подчёркивание.',
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'код',
            'value' => 'размер скидки',
            'min_order_amount' => 'минимальная сумма',
            'start_date' => 'дата начала',
            'end_date' => 'дата окончания',
        ];
    }
}
