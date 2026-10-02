<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Регистрация');
    }

    public function test_new_users_register_as_buyers_with_hashed_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'Тестовый Покупатель',
            'email' => 'test@example.com',
            'phone' => '+7 (900) 123-45-67',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertSame(UserRole::Buyer, $user->role);
        $this->assertNotSame('secret123', $user->password);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_registration_validates_input(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->post('/register', [
            'name' => '',
            'email' => 'taken@example.com',
            'phone' => '123',
            'password' => 'short',
            'password_confirmation' => 'other',
        ])->assertSessionHasErrors(['name', 'email', 'phone', 'password', 'terms']);

        $this->assertGuest();
    }
}
