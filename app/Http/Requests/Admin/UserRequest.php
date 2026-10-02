<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Rules\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Создание сотрудников (курьер, оператор, администратор) и редактирование пользователей.
 */
class UserRequest extends FormRequest
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
        $user = $this->route('user');
        $roles = array_map(fn (UserRole $role) => $role->value, UserRole::assignable());

        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:32', new Phone],
            'city_id' => ['nullable', 'integer', Rule::exists('cities', 'id')],
            // Роль производителя назначается только через одобрение заявки
            'role' => [$user?->isProducer() ? 'prohibited' : 'required', Rule::in($roles)],
            'password' => [$user ? 'nullable' : 'required', Password::min(8)->letters()->numbers()],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.prohibited' => 'Роль производителя меняется через раздел «Производители».',
        ];
    }
}
