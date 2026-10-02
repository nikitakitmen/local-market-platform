<?php

namespace Tests\Feature;

use App\Models\Producer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Доступ к разделам в зависимости от роли.
 */
class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/account')->assertRedirect(route('login'));
        $this->get('/cart')->assertRedirect(route('login'));
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_buyer_has_no_access_to_staff_producer_and_courier_areas(): void
    {
        $buyer = User::factory()->create();

        $this->actingAs($buyer)->get('/account')->assertOk();
        $this->actingAs($buyer)->get('/admin')->assertForbidden();
        $this->actingAs($buyer)->get('/producer')->assertForbidden();
        $this->actingAs($buyer)->get('/courier')->assertForbidden();
    }

    public function test_producer_can_open_cabinet_and_still_shop(): void
    {
        $producer = Producer::factory()->create();

        $this->actingAs($producer->user)->get('/producer')->assertOk();
        $this->actingAs($producer->user)->get('/cart')->assertOk();
        $this->actingAs($producer->user)->get('/admin')->assertForbidden();
    }

    public function test_courier_can_open_only_courier_panel(): void
    {
        $courier = User::factory()->courier()->create();

        $this->actingAs($courier)->get('/courier')->assertOk();
        $this->actingAs($courier)->get('/cart')->assertForbidden();
        $this->actingAs($courier)->get('/admin')->assertForbidden();
    }

    public function test_operator_has_limited_panel(): void
    {
        $operator = User::factory()->operator()->create();

        $this->actingAs($operator)->get('/admin')->assertOk();
        $this->actingAs($operator)->get('/admin/orders')->assertOk();
        $this->actingAs($operator)->get('/admin/reports')->assertOk();
        $this->actingAs($operator)->get('/admin/settings')->assertForbidden();
        $this->actingAs($operator)->get('/admin/users')->assertForbidden();
        $this->actingAs($operator)->get('/admin/categories')->assertForbidden();
    }

    public function test_admin_has_full_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/settings')->assertOk();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/categories')->assertOk();
    }
}
