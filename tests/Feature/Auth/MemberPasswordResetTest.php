<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class MemberPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_renders_at_public_url(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_password_reset_link_is_rate_limited_after_three_requests(): void
    {
        $user = User::factory()->member()->create();

        foreach (range(1, 3) as $i) {
            $this->post(route('auth.password.email'), [
                'email' => $user->email,
            ]);
        }

        $response = $this->post(route('auth.password.email'), [
            'email' => $user->email,
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_member_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->member()->create();

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
}
