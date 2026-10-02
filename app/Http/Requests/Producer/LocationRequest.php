<?php

namespace App\Http\Requests\Producer;

use App\Rules\Phone;
use Illuminate\Foundation\Http\FormRequest;

class LocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_pickup_point' => $this->boolean('is_pickup_point')]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:255'],
            'working_hours' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32', new Phone],
            'is_pickup_point' => ['boolean'],
        ];
    }
}
