<?php

namespace Tests\Feature\Auth;

use App\Mail\TwoFactorLoginEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'is_active' => true,
            'password' => Hash::make('Password123!'),
        ]);
    }

    public function test_admin_login_redirects_to_two_factor_when_enabled(): void
    {
        Config::set('labgoto.security.two_factor_admin', true);
        Mail::fake();

        $response = $this->post(route('auth.login.attempt'), [
            'email' => $this->admin->email,
            'password' => 'Password123!',
            'website' => '',
        ]);

        $response->assertRedirect();
        $this->assertMatchesRegularExpression('#/admin/login/2fa/[A-Za-z0-9]+$#', $response->headers->get('Location'));
        $this->assertGuest();
        Mail::assertQueued(TwoFactorLoginEmail::class);
    }

    public function test_admin_login_skips_two_factor_when_disabled(): void
    {
        Mail::fake();

        $response = $this->post(route('auth.login.attempt'), [
            'email' => $this->admin->email,
            'password' => 'Password123!',
            'website' => '',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin->fresh());
        Mail::assertNotSent(TwoFactorLoginEmail::class);
    }

    public function test_admin_can_complete_login_with_correct_code(): void
    {
        Config::set('labgoto.security.two_factor_admin', true);
        Mail::fake();

        $response = $this->post(route('auth.login.attempt'), [
            'email' => $this->admin->email,
            'password' => 'Password123!',
            'website' => '',
        ]);

        $location = $response->headers->get('Location');
        preg_match('#/admin/login/2fa/([A-Za-z0-9]+)$#', $location, $matches);
        $token = $matches[1];

        $code = null;
        Mail::assertQueued(TwoFactorLoginEmail::class, function (TwoFactorLoginEmail $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        $this->assertNotNull($code);

        Auth::logout();

        $verify = $this->post(route('auth.login.two-factor.verify', ['token' => $token]), [
            'code' => $code,
            'website' => '',
        ]);

        $verify->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin->fresh());
        $this->assertNotNull($this->admin->fresh()->last_login_at);
    }

    public function test_admin_login_rejects_wrong_code(): void
    {
        Config::set('labgoto.security.two_factor_admin', true);
        Mail::fake();

        $response = $this->post(route('auth.login.attempt'), [
            'email' => $this->admin->email,
            'password' => 'Password123!',
            'website' => '',
        ]);

        preg_match('#/admin/login/2fa/([A-Za-z0-9]+)$#', $response->headers->get('Location'), $matches);
        $token = $matches[1];

        $verify = $this->post(route('auth.login.two-factor.verify', ['token' => $token]), [
            'code' => '000000',
            'website' => '',
        ]);

        $verify->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_used_code_is_single_use(): void
    {
        Config::set('labgoto.security.two_factor_admin', true);
        Mail::fake();

        $response = $this->post(route('auth.login.attempt'), [
            'email' => $this->admin->email,
            'password' => 'Password123!',
            'website' => '',
        ]);

        preg_match('#/admin/login/2fa/([A-Za-z0-9]+)$#', $response->headers->get('Location'), $matches);
        $token = $matches[1];

        $code = null;
        Mail::assertQueued(TwoFactorLoginEmail::class, function (TwoFactorLoginEmail $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        $this->post(route('auth.login.two-factor.verify', ['token' => $token]), [
            'code' => $code,
            'website' => '',
        ]);

        Auth::logout();

        $second = $this->post(route('auth.login.two-factor.verify', ['token' => $token]), [
            'code' => $code,
            'website' => '',
        ]);

        $second->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_expired_token_returns_to_login(): void
    {
        Config::set('labgoto.security.two_factor_admin', true);
        Mail::fake();

        $response = $this->post(route('auth.login.attempt'), [
            'email' => $this->admin->email,
            'password' => 'Password123!',
            'website' => '',
        ]);

        preg_match('#/admin/login/2fa/([A-Za-z0-9]+)$#', $response->headers->get('Location'), $matches);
        $token = $matches[1];

        Cache::forget("2fa:pending:{$token}");

        $page = $this->get(route('auth.login.two-factor.show', ['token' => $token]));

        $page->assertRedirect(route('auth.login'));
        $page->assertSessionHasErrors('email');
    }

    public function test_resend_issues_a_new_code(): void
    {
        Config::set('labgoto.security.two_factor_admin', true);
        Mail::fake();

        $response = $this->post(route('auth.login.attempt'), [
            'email' => $this->admin->email,
            'password' => 'Password123!',
            'website' => '',
        ]);

        preg_match('#/admin/login/2fa/([A-Za-z0-9]+)$#', $response->headers->get('Location'), $matches);
        $token = $matches[1];

        $this->post(route('auth.login.two-factor.resend', ['token' => $token]), [
            'website' => '',
        ]);

        Mail::assertQueued(TwoFactorLoginEmail::class, 2);
    }

    public function test_two_factor_does_not_affect_member_login(): void
    {
        Config::set('labgoto.security.two_factor_admin', true);
        Mail::fake();

        $member = User::factory()->create([
            'user_type' => 'member',
            'is_active' => true,
            'password' => Hash::make('Member123!'),
        ]);

        $response = $this->post(route('login.submit'), [
            'email' => $member->email,
            'password' => 'Member123!',
            'website' => '',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($member->fresh());
        Mail::assertNotSent(TwoFactorLoginEmail::class);
    }
}