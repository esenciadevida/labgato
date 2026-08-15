<?php

namespace Tests\Feature\PublicSite;

use App\Models\Popup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PopupDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_popup_is_displayed_on_public_pages(): void
    {
        Popup::factory()->create([
            'is_active' => true,
            'title' => 'Seasonal Promo',
            'content' => 'Limited time offer for registered members.',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Seasonal Promo');
        $response->assertSee('Limited time offer for registered members.');
    }

    public function test_inactive_popup_is_not_displayed(): void
    {
        Popup::factory()->create([
            'is_active' => false,
            'title' => 'Hidden Popup',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('Hidden Popup');
    }

    public function test_active_popup_with_both_blank_title_and_content_is_not_displayed(): void
    {
        Popup::factory()->create([
            'is_active' => true,
            'title' => '   ',
            'content' => "\n \n",
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('labgato-popup-dismissed-');
    }

    public function test_popup_with_blank_title_still_displays_content(): void
    {
        Popup::factory()->create([
            'is_active' => true,
            'title' => '   ',
            'content' => 'Valid content',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Valid content');
    }

    public function test_popup_with_blank_content_still_displays_title(): void
    {
        Popup::factory()->create([
            'is_active' => true,
            'title' => 'Valid title',
            'content' => "\n \n",
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Valid title');
    }

    public function test_popup_is_not_displayed_on_auth_pages(): void
    {
        Popup::factory()->create([
            'is_active' => true,
            'title' => 'Auth Page Popup',
            'content' => 'Should not appear on login.',
        ]);

        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertDontSee('Auth Page Popup');
    }

    public function test_popup_renders_dismiss_key_for_local_storage(): void
    {
        $popup = Popup::factory()->create(['is_active' => true]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('labgato-popup-dismissed-'.$popup->id, false);
    }

    public function test_popup_respects_schedule_window(): void
    {
        Popup::factory()->create([
            'is_active' => true,
            'title' => 'Scheduled Later Popup',
            'content' => 'Not yet visible.',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('Scheduled Later Popup');
    }

    public function test_after_delay_popup_renders_delay_configuration(): void
    {
        Popup::factory()->create([
            'is_active' => true,
            'trigger_type' => Popup::TRIGGER_AFTER_DELAY,
            'delay_seconds' => 5,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('setTimeout', false);
        $response->assertSee('5000', false);
    }

    public function test_on_load_popup_renders_without_delay(): void
    {
        Popup::factory()->create([
            'is_active' => true,
            'trigger_type' => Popup::TRIGGER_ON_LOAD,
            'delay_seconds' => 0,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('setTimeout');
        $response->assertSee('this.open = true;', false);
    }

    public function test_popup_renders_cta_link(): void
    {
        Popup::factory()->create([
            'is_active' => true,
            'cta_text' => 'Join now',
            'cta_url' => 'https://example.com/join',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Join now');
        $response->assertSee('https://example.com/join', false);
    }
}
