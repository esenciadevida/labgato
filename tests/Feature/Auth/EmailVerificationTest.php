<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notice_page_renders_for_unverified_member(): void
    {
        config(['labgoto.auth.email_verification' => true]);

        $user = User::factory()->member()->unverified()->create();

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertStatus(200);
    }

    public function test_notice_redirects_when_verification_disabled(): void
    {
        $user = User::factory()->member()->create();

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertRedirect(route('account.dashboard'));
    }

    public function test_verification_link_marks_email_as_verified(): void
    {
        $user = User::factory()->member()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->actingAs($user)->get($url);

        $response->assertRedirect(route('account.dashboard'));

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $user = User::factory()->member()->unverified()->create();

        $response = $this->actingAs($user)->get('/email/verify/'.$user->id.'/tampered-hash');

        $response->assertStatus(403);
    }

    public function test_resend_verification_email(): void
    {
        Notification::fake();

        config(['labgoto.auth.email_verification' => true]);

        $user = User::factory()->member()->unverified()->create();

        $response = $this->actingAs($user)->post(route('verification.send'));

        $response->assertSessionHas('status');

        Notification::assertSentOnDemand(
            VerifyEmail::class,
            function ($notification, $channels, $notifiable) use ($user) {
                return $notifiable->routes['mail'] === $user->email;
            }
        );
    }
}
