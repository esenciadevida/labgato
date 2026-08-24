<?php

namespace Tests\Feature\PublicSite;

use App\Models\ContactMessage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Notifications\NewContactMessageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('contact-127.0.0.1');
    }

    private function superAdmin(): User
    {
        $permission = Permission::factory()->create(['slug' => 'admin.full-access']);
        $role = Role::factory()->create();
        $role->permissions()->attach($permission);
        $user = User::factory()->create(['is_active' => true]);
        $user->roles()->attach($role);

        return $user;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Question about reference ranges',
            'message' => 'This is a test message that is long enough to pass validation.',
        ], $overrides);
    }

    public function test_contact_page_renders(): void
    {
        $response = $this->get(route('contact.create'));

        $response->assertOk();
        $response->assertSee('name', false);
        $response->assertSee('email', false);
        $response->assertSee('subject', false);
        $response->assertSee('message', false);
    }

    public function test_submitting_a_valid_message_stores_it_with_a_reference_code(): void
    {
        $response = $this->post(route('contact.store'), $this->validPayload());

        $response->assertRedirect(route('contact.create'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('reference_code');

        $message = ContactMessage::firstOrFail();
        $this->assertSame('Jane Doe', $message->name);
        $this->assertSame('new', $message->status);
        $this->assertMatchesRegularExpression('/^MSG-\d{6}$/', $message->reference_code);
    }

    public function test_validation_rejects_missing_fields(): void
    {
        $response = $this->post(route('contact.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_authenticated_user_gets_name_email_prefilled_and_user_id_set(): void
    {
        $member = User::factory()->member()->create();

        $response = $this->actingAs($member)->post(route('contact.store'), $this->validPayload());

        $response->assertRedirect(route('contact.create'));

        $message = ContactMessage::firstOrFail();
        $this->assertSame($member->id, $message->user_id);
        $this->assertSame($member->name, $message->name);
        $this->assertSame($member->email, $message->email);
    }

    public function test_rate_limiting_blocks_the_sixth_submission_within_an_hour(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('contact.store'), $this->validPayload(['subject' => 'Subject '.$i]));
        }

        $response = $this->post(route('contact.store'), $this->validPayload());

        $response->assertStatus(429);
    }

    public function test_new_message_notifies_super_admins(): void
    {
        Notification::fake();

        $superAdmin = $this->superAdmin();
        $plainAdmin = User::factory()->create(['is_active' => true]);

        $this->post(route('contact.store'), $this->validPayload());

        Notification::assertSentTo($superAdmin, NewContactMessageNotification::class);
        Notification::assertNotSentTo($plainAdmin, NewContactMessageNotification::class);
    }

    public function test_new_message_is_also_sent_to_the_configured_contact_inbox(): void
    {
        Notification::fake();
        config(['labgoto.contact.notify_email' => 'hello@labgoto.com']);

        $this->post(route('contact.store'), $this->validPayload());

        Notification::assertSentOnDemand(NewContactMessageNotification::class, function (NewContactMessageNotification $notification, array $channels, object $notifiable) {
            return $notifiable->routes['mail'] === 'hello@labgoto.com';
        });
    }

    public function test_no_extra_copy_is_sent_when_contact_notify_email_is_not_configured(): void
    {
        Notification::fake();
        config(['labgoto.contact.notify_email' => null]);

        $this->post(route('contact.store'), $this->validPayload());

        Notification::assertNothingSent();
    }

    public function test_super_admin_notification_links_directly_to_the_stored_message(): void
    {
        Mail::fake();

        $superAdmin = $this->superAdmin();

        $this->post(route('contact.store'), $this->validPayload());

        $notification = $superAdmin->fresh()->unreadNotifications()->first();

        $this->assertNotNull($notification);
        $this->assertSame('contact_message', data_get($notification, 'data.type'));
        $this->assertSame('Read', data_get($notification, 'data.action_text'));
        $this->assertSame(
            route('admin.contact-messages.show', ContactMessage::firstOrFail()),
            data_get($notification, 'data.action_url'),
        );
    }
}
