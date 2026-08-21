<?php

namespace Tests\Feature\Admin;

use App\Models\ContactMessage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ContactMessageRepliedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $permission = Permission::factory()->create(['slug' => 'admin.full-access']);
        $role = Role::factory()->create();
        $role->permissions()->attach($permission);
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->attach($role);

        return $user;
    }

    private function message(array $attributes = []): ContactMessage
    {
        return ContactMessage::create(array_merge([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Question about reference ranges',
            'message' => 'This is a test message that is long enough to pass validation.',
            'status' => 'new',
        ], $attributes));
    }

    public function test_super_admin_can_view_the_contact_inbox(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->get(route('admin.contact-messages.index'));

        $response->assertOk();
    }

    public function test_non_super_admin_cannot_view_the_contact_inbox(): void
    {
        $admin = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->get(route('admin.contact-messages.index'));

        $response->assertForbidden();
    }

    public function test_super_admin_can_view_a_message_detail(): void
    {
        $admin = $this->superAdmin();
        $message = $this->message();

        $response = $this->actingAs($admin)->get(route('admin.contact-messages.show', $message));

        $response->assertOk();
        $response->assertSee('Question about reference ranges');
        $response->assertSee('Jane Doe');
    }

    public function test_super_admin_can_reply_to_a_message(): void
    {
        Notification::fake();
        $admin = $this->superAdmin();
        $member = User::factory()->member()->create();
        $message = $this->message(['user_id' => $member->id]);

        $response = $this->actingAs($admin)->post(
            route('admin.contact-messages.reply', $message),
            ['reply' => 'Thank you for your question. Here is the answer to your query.'],
        );

        $response->assertRedirect(route('admin.contact-messages.show', $message));
        $response->assertSessionHas('success');

        $message->refresh();
        $this->assertSame('replied', $message->status);
        $this->assertDatabaseHas('contact_replies', [
            'contact_message_id' => $message->id,
            'admin_id' => $admin->id,
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'contact-messages.replied']);

        Notification::assertSentTo($member, ContactMessageRepliedNotification::class);
    }

    public function test_reply_notifies_guest_sender_by_email(): void
    {
        Notification::fake();
        $admin = $this->superAdmin();
        $message = $this->message();

        $this->actingAs($admin)->post(
            route('admin.contact-messages.reply', $message),
            ['reply' => 'Thank you for reaching out. We have answered your query below.'],
        );

        Notification::assertSentOnDemand(
            ContactMessageRepliedNotification::class,
            function (ContactMessageRepliedNotification $notification, array $channels, object $notifiable) {
                return $notifiable->routes['mail'] === 'jane@example.com';
            }
        );
    }

    public function test_reply_requires_valid_input(): void
    {
        $admin = $this->superAdmin();
        $message = $this->message();

        $response = $this->actingAs($admin)->post(
            route('admin.contact-messages.reply', $message),
            ['reply' => 'short'],
        );

        $response->assertSessionHasErrors('reply');
        $this->assertDatabaseCount('contact_replies', 0);
        $message->refresh();
        $this->assertSame('new', $message->status);
    }

    public function test_admin_without_reply_permission_cannot_reply(): void
    {
        $permission = Permission::factory()->create(['slug' => 'contact-messages.view']);
        $role = Role::factory()->create();
        $role->permissions()->attach($permission);
        $admin = User::factory()->create(['is_active' => true]);
        $admin->roles()->attach($role);
        $message = $this->message();

        $response = $this->actingAs($admin)->post(
            route('admin.contact-messages.reply', $message),
            ['reply' => 'Thank you for reaching out. We have answered your query below.'],
        );

        $response->assertForbidden();
        $this->assertDatabaseCount('contact_replies', 0);
    }
}
