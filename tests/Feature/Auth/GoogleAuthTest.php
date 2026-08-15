<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogleUser(
        string $name = 'Google User',
        string $email = 'google.user@example.com',
        string $id = 'google-id-123',
    ): void {
        Socialite::fake('google', SocialiteUser::fake([
            'id' => $id,
            'name' => $name,
            'email' => $email,
        ]));
    }

    public function test_google_redirect_route_exists(): void
    {
        Socialite::fake('google');

        $response = $this->get(route('auth.google'));

        $response->assertStatus(302);
        $response->assertRedirect('https://socialite.fake/google/authorize');
    }

    public function test_google_callback_creates_new_member(): void
    {
        $this->fakeGoogleUser(
            name: 'New Google Member',
            email: 'new.google.member@example.com',
            id: 'google-id-456',
        );

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('account.dashboard'));

        $this->assertDatabaseHas('users', [
            'name' => 'New Google Member',
            'email' => 'new.google.member@example.com',
            'google_id' => 'google-id-456',
            'user_type' => 'member',
            'is_active' => true,
        ]);

        $user = User::where('email', 'new.google.member@example.com')->firstOrFail();

        $this->assertNotNull($user->email_verified_at);
        $this->assertNotNull($user->profile_slug);
        $this->assertNotNull($user->last_login_at);
        $this->assertTrue($user->hasGoogleAccount());
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_callback_logs_in_existing_member(): void
    {
        $member = User::factory()->member()->create([
            'email' => 'google.user@example.com',
            'google_id' => null,
        ]);

        $this->fakeGoogleUser(email: 'google.user@example.com');

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('account.dashboard'));
        $this->assertAuthenticatedAs($member);

        $member->refresh();
        $this->assertSame('google-id-123', $member->google_id);
        $this->assertNotNull($member->last_login_at);
    }

    public function test_google_callback_rejects_admin_email(): void
    {
        User::factory()->create([
            'email' => 'google.user@example.com',
            'user_type' => 'admin',
        ]);

        $this->fakeGoogleUser(email: 'google.user@example.com');

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest();

        $this->assertDatabaseHas('users', [
            'email' => 'google.user@example.com',
            'google_id' => null,
            'user_type' => 'admin',
        ]);
    }

    public function test_google_callback_sets_email_verified(): void
    {
        $this->fakeGoogleUser(email: 'verified.member@example.com');

        $this->get(route('auth.google.callback'));

        $user = User::where('email', 'verified.member@example.com')->firstOrFail();

        $this->assertNotNull($user->email_verified_at);
    }

    public function test_google_callback_handles_socialite_exception(): void
    {
        Socialite::fake('google', function () {
            throw new \Exception('access_denied');
        });

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest();
    }
}
