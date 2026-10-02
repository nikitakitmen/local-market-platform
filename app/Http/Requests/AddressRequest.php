<?php

namespace App\Http\Requests;

use App\Models\Address;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:50'],
            'city_id' => ['required', 'integer', Rule::exists('cities', 'id')->where('is_active', true)],
            'street' => ['required', 'string', 'max:255'],
            'apartment' => ['nullable', 'string', 'max:20'],
            'entrance' => ['nullable', 'string', 'max:10'],
            'floor' => ['nullable', 'string', 'max:10'],
            'distance_km' => ['required', 'integer', Rule::in(array_keys(Address::DISTANCE_ZONES))],
            'comment' => ['nullable', 'string', 'max:255'],
            'is_default' => ['boolean'],
        ];
    }
}
