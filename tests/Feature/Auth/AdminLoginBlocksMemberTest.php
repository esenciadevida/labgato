<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginBlocksMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_is_served_at_admin_path(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('Welcome back');
    }

    public function test_member_cannot_login_via_admin_login(): void
    {
        $user = User::factory()->member()->create(['is_active' => true]);

        $response = $this->post(route('auth.login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_login_via_admin_login(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->post(route('auth.login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
