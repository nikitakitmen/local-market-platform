<?php

namespace App\Http\Requests\Admin;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:100'],
            'support_email' => ['required', 'email', 'max:255'],
            'support_phone' => ['required', 'string', 'max:32'],
            'platform_commission_percent' => ['required', 'numeric', 'min:0', 'max:50'],
            'min_order_amount' => ['required', 'numeric', 'min:0', 'max:100000'],
            'delivery_base_cost' => ['required', 'numeric', 'min:0', 'max:10000'],
            'delivery_cost_per_km' => ['required', 'numeric', 'min:0', 'max:1000'],
            'delivery_free_from' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ];
    }

    public function attributes(): array
    {
        return array_map('mb_strtolower', Setting::LABELS);
    }
}
