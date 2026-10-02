<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Address;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Сотрудники и покупатели. Пароль всех демо-аккаунтов: password.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $kazan = City::where('name', 'Казань')->first();
        $nn = City::where('name', 'Нижний Новгород')->first();
        $password = Hash::make('password');

        $staff = [
            ['Администратор Платформы', 'admin@localmarket.test', '+7 (900) 000-00-01', UserRole::Admin, null],
            ['Ольга Новикова', 'operator@localmarket.test', '+7 (900) 000-00-02', UserRole::Operator, null],
            ['Алексей Быстров', 'courier@localmarket.test', '+7 (900) 000-00-03', UserRole::Courier, $kazan],
            ['Сергей Ветров', 'courier2@localmarket.test', '+7 (900) 000-00-04', UserRole::Courier, $nn],
        ];

        foreach ($staff as [$name, $email, $phone, $role, $city]) {
            User::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'city_id' => $city?->id,
                'role' => $role,
                'password' => $password,
                'email_verified_at' => now(),
            ]);
        }

        $buyers = [
            ['Анна Смирнова', 'buyer@localmarket.test', '+7 (917) 111-22-33', $kazan, [
                ['Дом', 'ул. Баумана, д. 15', '21', '2', '4', 3],
                ['Работа', 'ул. Пушкина, д. 5', null, null, '3', 7],
            ]],
            ['Мария Иванова', 'maria@localmarket.test', '+7 (917) 222-33-44', $kazan, [
                ['Дом', 'пр. Победы, д. 100', '45', '1', '7', 12],
            ]],
            ['Дмитрий Козлов', 'dmitry@localmarket.test', '+7 (917) 333-44-55', $kazan, [
                ['Дом', 'ул. Чистопольская, д. 33', '8', '3', '2', 7],
            ]],
            ['Елена Петрова', 'elena@localmarket.test', '+7 (920) 444-55-66', $nn, [
                ['Дом', 'ул. Большая Покровская, д. 20', '12', '1', '3', 3],
            ]],
            ['Игорь Волков', 'igor@localmarket.test', '+7 (917) 555-66-77', $kazan, [
                ['Дом', 'ул. Декабристов, д. 81', '64', '2', '9', 7],
            ]],
        ];

        foreach ($buyers as [$name, $email, $phone, $city, $addresses]) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'city_id' => $city->id,
                'role' => UserRole::Buyer,
                'password' => $password,
                'email_verified_at' => now(),
            ]);

            foreach ($addresses as $index => [$title, $street, $apartment, $entrance, $floor, $distance]) {
                $user->addresses()->save(new Address([
                    'city_id' => $city->id,
                    'title' => $title,
                    'street' => $street,
                    'apartment' => $apartment,
                    'entrance' => $entrance,
                    'floor' => $floor,
                    'distance_km' => $distance,
                    'is_default' => $index === 0,
                ]));
            }
        }
    }
}
