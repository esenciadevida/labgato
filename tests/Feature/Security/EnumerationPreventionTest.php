<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnumerationPreventionTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_unknown_email_returns_generic_error(): void
    {
        $response = $this->post(route('auth.login.attempt'), [
            'email' => 'nobody@example.com',
            'password' => 'StrongPass1234',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionHasErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function test_password_reset_with_unknown_email_returns_generic_success(): void
    {
        $response = $this->post(route('auth.password.email'), [
            'email' => 'nobody@example.com',
        ]);

        $response->assertSessionHas(
            'status',
            'If an account with that email exists, a password reset link has been sent.'
        );
    }
}
