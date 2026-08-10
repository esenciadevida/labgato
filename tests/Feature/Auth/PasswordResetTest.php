<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_renders_for_guests(): void
    {
        $response = $this->get(route('auth.password.request'));

        $response->assertStatus(200);
    }

    public function test_reset_link_status_message_is_displayed_without_revealing_email_existence(): void
    {
        $response = $this->post(route('auth.password.email'), [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertSessionHas('status');
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create();

        $token = Password::createToken($user);

        $response = $this->put(route('auth.password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword1234',
            'password_confirmation' => 'NewPassword1234',
        ]);

        $response->assertRedirect(route('auth.login'));
        $this->assertGuest();

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword1234', $user->password));
    }

    public function test_password_reset_requires_minimum_length(): void
    {
        $user = User::factory()->create();

        $token = Password::createToken($user);

        $response = $this->put(route('auth.password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_reset_token_cannot_be_reused(): void
    {
        $user = User::factory()->create();

        $token = Password::createToken($user);

        $this->put(route('auth.password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword1234',
            'password_confirmation' => 'NewPassword1234',
        ]);

        $response = $this->put(route('auth.password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'AnotherPassword1234',
            'password_confirmation' => 'AnotherPassword1234',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
