<?php

namespace Tests\Feature\PublicSite;

use App\Models\Announcement;
use App\Models\Popup;
use App\Services\PublicNoticeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PopupAnnouncementDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_popup_renders_on_public_pages(): void
    {
        Popup::factory()->create([
            'is_active' => true,
            'title' => 'Welcome Popup',
            'content' => 'Welcome to Labgoto.',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Welcome Popup');
        $response->assertSee('Welcome to Labgoto.');
    }

    public function test_active_announcement_renders_on_public_pages(): void
    {
        Announcement::factory()->create([
            'is_active' => true,
            'message' => 'Planned maintenance this weekend.',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Planned maintenance this weekend.');
    }

    public function test_both_popup_and_announcement_render_together(): void
    {
        Popup::factory()->create([
            'is_active' => true,
            'title' => 'Featured Tool Popup',
        ]);
        Announcement::factory()->create([
            'is_active' => true,
            'message' => 'New reference ranges published.',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Featured Tool Popup');
        $response->assertSee('New reference ranges published.');
    }

    public function test_inactive_popup_does_not_render(): void
    {
        Popup::factory()->create([
            'is_active' => false,
            'title' => 'Hidden Popup',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('Hidden Popup');
    }

    public function test_inactive_announcement_does_not_render(): void
    {
        Announcement::factory()->create([
            'is_active' => false,
            'message' => 'Hidden Announcement',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('Hidden Announcement');
    }

    public function test_scheduled_outside_window_does_not_render(): void
    {
        Popup::factory()->create([
            'is_active' => true,
            'title' => 'Expired Popup',
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subDays(5),
        ]);

        Announcement::factory()->create([
            'is_active' => true,
            'message' => 'Future Announcement',
            'starts_at' => now()->addDays(5),
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('Expired Popup');
        $response->assertDontSee('Future Announcement');
    }

    public function test_notice_service_provides_data_to_layout(): void
    {
        $popup = Popup::factory()->create(['is_active' => true]);
        $announcement = Announcement::factory()->create(['is_active' => true]);

        $service = app(PublicNoticeService::class);

        $this->assertSame($popup->id, $service->activePopup()?->id);
        $this->assertTrue($service->activeAnnouncements()->contains($announcement));
    }
}
