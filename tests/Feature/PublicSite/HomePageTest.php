<?php

namespace Tests\Feature\PublicSite;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_successfully(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Labgoto');
        $response->assertSee('Lab Calculators, Reference Ranges & SOPs');
    }

    public function test_home_page_includes_public_navigation(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Calculators');
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

    public function test_authenticated_header_wires_notifications_and_user_menu_to_shared_panel_bus(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertSee('labgato-open-panel', false);
        $response->assertSee("detail: 'panel'", false);
        $response->assertSee("detail: 'dropdown'", false);
    }
}
