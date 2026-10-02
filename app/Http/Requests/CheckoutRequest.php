<?php

namespace App\Http\Requests;

use App\Enums\DeliveryMethod;
use App\Enums\PaymentMethod;
use App\Models\Address;
use App\Rules\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Данные оформления заказа: способ получения, адрес, получатель, оплата, комментарий.
 */
class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isDelivery = $this->input('delivery_method') === DeliveryMethod::Delivery->value;
        $isNewAddress = $isDelivery && $this->input('address_id') === 'new';

        return [
            'delivery_method' => ['required', Rule::enum(DeliveryMethod::class)],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'recipient_name' => ['required', 'string', 'min:2', 'max:100'],
            'recipient_phone' => ['required', 'string', 'max:32', new Phone],
            'comment' => ['nullable', 'string', 'max:1000'],

            // Сохранённый адрес пользователя или новый адрес
            'address_id' => [Rule::requiredIf($isDelivery), 'nullable'],
            'new_address.city_id' => [Rule::requiredIf($isNewAddress), 'nullable', 'integer', Rule::exists('cities', 'id')->where('is_active', true)],
            'new_address.street' => [Rule::requiredIf($isNewAddress), 'nullable', 'string', 'max:255'],
            'new_address.apartment' => ['nullable', 'string', 'max:20'],
            'new_address.entrance' => ['nullable', 'string', 'max:10'],
            'new_address.floor' => ['nullable', 'string', 'max:10'],
            'new_address.distance_km' => [Rule::requiredIf($isNewAddress), 'nullable', 'integer', Rule::in(array_keys(Address::DISTANCE_ZONES))],
            'save_address' => ['boolean'],

            // Пункт самовывоза для каждого производителя: pickup_locations[producer_id] = location_id
            'pickup_locations' => ['nullable', 'array'],
            'pickup_locations.*' => ['nullable', 'integer'],
        ];
    }

    public function attributes(): array
    {
        return [
            'address_id' => 'адрес доставки',
            'new_address.city_id' => 'город',
            'new_address.street' => 'улица и дом',
            'new_address.apartment' => 'квартира',
            'new_address.distance_km' => 'удалённость',
            'recipient_name' => 'имя получателя',
            'recipient_phone' => 'телефон получателя',
        ];
    }
}
