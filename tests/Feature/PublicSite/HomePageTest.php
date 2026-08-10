<?php

namespace Tests\Feature\PublicSite;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_successfully(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('LabGato');
        $response->assertSee('Professional Laboratory Toolkit');
    }

    public function test_home_page_includes_public_navigation(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Home');
    }

    public function test_home_page_includes_footer_legal_links(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee(route('legal.privacy-policy'));
        $response->assertSee(route('legal.terms-of-service'));
        $response->assertSee(route('legal.disclaimer'));
        $response->assertSee(route('legal.accessibility-statement'));
    }

    public function test_home_page_does_not_display_fake_statistics(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertDontSee('Total Samples');
        $response->assertDontSee('Active Users');
        $response->assertDontSee('Tests Completed');
        $response->assertDontSee('statistics');
    }
}
