<?php

namespace Tests\Feature\Notifications;

use App\Models\Role;
use App\Models\User;
use App\Notifications\NewContactMessageNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminNotificationsPageTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::factory()->create(['is_active' => true]);
        $admin->roles()->attach(
            Role::where('slug', 'super-administrator')->firstOrFail()
        );

        return $admin;
    }

    public function test_admin_notifications_page_lists_notifications(): void
    {
        Notification::fake();

        $admin = $this->superAdmin();
        $admin->notify(new NewContactMessageNotification(
            'Ada Lovelace',
            'ada@example.com',
            'Question about the challenge',
            'Please tell me more about the ASCPI challenge.',
        ));

        $this->actingAs($admin)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('Contact Message', false)
            ->assertSee('Read');
    }

    public function test_admin_can_mark_notification_as_read_via_shared_endpoint(): void
    {
        $admin = $this->superAdmin();
        $admin->notifications()->create([
            'id' => Str::uuid()->toString(),
            'type' => NewContactMessageNotification::class,
            'data' => [
                'type' => 'contact_message',
                'message' => 'New contact message from Ada Lovelace.',
                'action_text' => 'Read',
                'action_url' => route('admin.contact-messages.index'),
            ],
        ]);

        $notification = $admin->unreadNotifications->first();

        $this->actingAs($admin)
            ->post(route('notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertSame(0, $admin->fresh()->unreadNotifications()->count());
    }

    public function test_admin_bell_links_to_the_admin_notifications_area(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.notifications.index'), false);
    }
}
