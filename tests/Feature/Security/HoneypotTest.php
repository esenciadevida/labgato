<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HoneypotTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_with_filled_honeypot_is_blocked(): void
    {
        $response = $this->from(route('register'))->post(route('register.submit'), [
            'name' => 'Spam Bot',
            'email' => 'bot@example.com',
            'password' => 'StrongPass1234',
            'password_confirmation' => 'StrongPass1234',
            'website' => 'http://spam.example.com',
        ]);

        $response->assertRedirect(route('register'));
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_register_with_empty_honeypot_allows_registration(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Jane Member',
            'email' => 'jane@example.com',
            'password' => 'StrongPass1234',
            'password_confirmation' => 'StrongPass1234',
            'professional_status' => 'student',
        ]);

        $response->assertRedirect(route('account.dashboard'));
        $this->assertDatabaseCount('users', 1);
        $this->assertAuthenticated();
    }

    public function test_login_with_filled_honeypot_is_blocked(): void
    {
        $response = $this->from(route('login'))->post(route('login.submit'), [
            'email' => 'jane@example.com',
            'password' => 'StrongPass1234',
            'website' => 'http://spam.example.com',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_contact_with_filled_honeypot_is_blocked(): void
    {
        $response = $this->from(route('contact.create'))->post(route('contact.store'), [
            'name' => 'Spam Bot',
            'email' => 'bot@example.com',
            'subject' => 'Buy stuff',
            'message' => 'Spam message here',
            'website' => 'http://spam.example.com',
        ]);

        $response->assertRedirect(route('contact.create'));
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_contact_with_empty_honeypot_allows_submission(): void
    {
        $response = $this->post(route('contact.store'), [
            'name' => 'Jane Member',
            'email' => 'jane@example.com',
            'subject' => 'Question',
            'message' => 'This is a valid inquiry message.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseCount('contact_messages', 1);
    }
}
