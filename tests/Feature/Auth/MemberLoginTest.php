<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_login_page_renders_for_guests(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('email');
        $response->assertSee('password');
    }

    public function test_member_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->member()->create(['is_active' => true]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('account.dashboard'));
        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
    }

    public function test_member_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->member()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_member_cannot_login(): void
    {
        $user = User::factory()->member()->create(['is_active' => false]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_member_login_attempts_are_throttled(): void
    {
        $user = User::factory()->member()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_cannot_login_via_member_login(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('auth.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_member_can_logout(): void
    {
        $user = User::factory()->member()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
