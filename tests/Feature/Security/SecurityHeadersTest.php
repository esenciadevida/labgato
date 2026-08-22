<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_standard_security_headers_are_present(): void
    {
        $response = $this->get(route('home'));

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');
        $response->assertHeader('Permissions-Policy');
        $response->assertHeader('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        $response->assertHeader('Cross-Origin-Resource-Policy', 'same-origin');
    }

    public function test_hsts_is_added_when_request_is_secure(): void
    {
        $response = $this->get('https://localhost/');

        $response->assertHeader('Strict-Transport-Security', 'max-age=63072000; includeSubDomains; preload');
    }

    public function test_hsts_is_not_added_on_plain_http(): void
    {
        $response = $this->get('http://localhost/');

        $response->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_content_security_policy_is_present(): void
    {
        $response = $this->get(route('home'));

        $response->assertHeader('Content-Security-Policy');
        $this->assertStringContainsString(
            "frame-ancestors 'self'",
            (string) $response->headers->get('Content-Security-Policy'),
        );
    }
}
