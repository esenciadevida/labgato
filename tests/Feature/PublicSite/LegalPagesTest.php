<?php

namespace Tests\Feature\PublicSite;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_policy_page_renders_successfully(): void
    {
        $response = $this->get(route('legal.privacy-policy'));

        $response->assertStatus(200);
        $response->assertSee('Privacy Policy');
    }

    public function test_terms_of_service_page_renders_successfully(): void
    {
        $response = $this->get(route('legal.terms-of-service'));

        $response->assertStatus(200);
        $response->assertSee('Terms of Service');
    }

    public function test_disclaimer_page_renders_successfully(): void
    {
        $response = $this->get(route('legal.disclaimer'));

        $response->assertStatus(200);
        $response->assertSee('Disclaimer');
    }

    public function test_accessibility_statement_page_renders_successfully(): void
    {
        $response = $this->get(route('legal.accessibility-statement'));

        $response->assertStatus(200);
        $response->assertSee('Accessibility Statement');
    }

    public function test_cookie_policy_page_renders_successfully(): void
    {
        $response = $this->get(route('legal.cookie-policy'));

        $response->assertStatus(200);
        $response->assertSee('Cookie Policy');
    }

    public function test_legal_pages_include_disclaimer_about_clinical_judgment(): void
    {
        $routes = [
            'legal.privacy-policy',
            'legal.terms-of-service',
            'legal.disclaimer',
            'legal.accessibility-statement',
            'legal.cookie-policy',
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $response->assertStatus(200);
        }

        $disclaimer = $this->get(route('legal.disclaimer'));
        $disclaimer->assertSee('does not replace professional clinical judgment', false);
        $disclaimer->assertSee('not a diagnostic tool', false);
    }
}
