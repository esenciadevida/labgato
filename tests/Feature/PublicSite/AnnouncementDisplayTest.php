<?php

namespace Tests\Feature\PublicSite;

use App\Models\Announcement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_announcement_banner_is_displayed(): void
    {
        Announcement::factory()->create([
            'is_active' => true,
            'message' => 'Scheduled maintenance tonight at 8 PM.',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Scheduled maintenance tonight at 8 PM.');
    }

    public function test_inactive_announcement_is_not_displayed(): void
    {
        Announcement::factory()->create([
            'is_active' => false,
            'message' => 'Hidden banner',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('Hidden banner');
    }

    public function test_announcement_type_affects_color(): void
    {
        Announcement::factory()->create([
            'is_active' => true,
            'type' => 'warning',
            'message' => 'Service advisory',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('data-type="warning"', false);
        $response->assertSee('bg-amber-500', false);
    }

    public function test_dismissible_announcement_renders_dismiss_key_and_button(): void
    {
        $announcement = Announcement::factory()->create([
            'is_active' => true,
            'is_dismissible' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('labgato-dismissed-announcement-'.$announcement->id, false);
        $response->assertSee('Dismiss announcement');
    }

    public function test_non_dismissible_announcement_has_no_dismiss_button(): void
    {
        Announcement::factory()->create([
            'is_active' => true,
            'is_dismissible' => false,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('Dismiss announcement');
    }

    public function test_announcement_respects_schedule_window(): void
    {
        Announcement::factory()->create([
            'is_active' => true,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
            'message' => 'Future banner',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('Future banner');
    }

    public function test_multiple_active_announcements_stack(): void
    {
        Announcement::factory()->create(['is_active' => true, 'message' => 'First banner']);
        Announcement::factory()->create(['is_active' => true, 'message' => 'Second banner']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('First banner');
        $response->assertSee('Second banner');
    }
}
