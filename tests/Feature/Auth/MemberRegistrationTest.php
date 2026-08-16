<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MemberRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_renders_for_guests(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('name');
        $response->assertSee('email');
        $response->assertSee('password');
    }

    public function test_member_can_register_with_valid_details(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Jane Member',
            'email' => 'jane@example.com',
            'password' => 'StrongPass1234',
            'password_confirmation' => 'StrongPass1234',
        ]);

        $response->assertRedirect(route('account.dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'jane@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('member', $user->user_type);
        $this->assertTrue((bool) $user->is_active);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_registration_requires_valid_password(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Jane Member',
            'email' => 'jane@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        $existing = User::factory()->create(['email' => 'jane@example.com']);

        $response = $this->post(route('register.submit'), [
            'name' => 'Jane Member',
            'email' => $existing->email,
            'password' => 'StrongPass1234',
            'password_confirmation' => 'StrongPass1234',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_registration_sends_verification_email_when_enabled(): void
    {
        Notification::fake();

        config(['labgoto.auth.email_verification' => true]);

        $response = $this->post(route('register.submit'), [
            'name' => 'Jane Member',
            'email' => 'jane@example.com',
            'password' => 'StrongPass1234',
            'password_confirmation' => 'StrongPass1234',
        ]);

        $response->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'jane@example.com')->first();
        $this->assertNull($user->email_verified_at);

        Notification::assertSentOnDemand(
            VerifyEmail::class,
            function ($notification, $channels, $notifiable) use ($user) {
                return $notifiable->routes['mail'] === $user->email;
            }
        );
    }
}
