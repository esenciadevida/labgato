<?php

namespace Tests\Feature\Member;

use App\Models\ContactMessage;
use App\Models\ContactReply;
use App\Models\User;
use App\Notifications\ContactMessageRepliedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MemberMessagesTest extends TestCase
{
    use RefreshDatabase;

    private function messageFor(User $member, string $subject = 'My question'): ContactMessage
    {
        $message = ContactMessage::create([
            'user_id' => $member->id,
            'name' => $member->name,
            'email' => $member->email,
            'subject' => $subject,
            'message' => 'This is a test message that is long enough to pass validation.',
            'status' => 'replied',
        ]);

        return $message;
    }

    public function test_guest_cannot_access_messages_page(): void
    {
        $response = $this->get(route('account.messages'));

        $response->assertRedirect(route('auth.login'));
    }

    public function test_member_can_view_their_messages_page(): void
    {
        $member = User::factory()->member()->create();

        $response = $this->actingAs($member)->get(route('account.messages'));

        $response->assertOk();
    }

    public function test_member_sees_their_own_messages_and_replies(): void
    {
        $member = User::factory()->member()->create();
        $admin = User::factory()->create();
        $message = $this->messageFor($member, 'My lab question');
        ContactReply::create([
            'contact_message_id' => $message->id,
            'admin_id' => $admin->id,
            'reply' => 'Thank you for your message. Here is our answer.',
        ]);

        $response = $this->actingAs($member)->get(route('account.messages'));

        $response->assertOk();
        $response->assertSee('My lab question');
        $response->assertSee('Thank you for your message. Here is our answer.');
    }

    public function test_member_does_not_see_other_members_messages(): void
    {
        $member = User::factory()->member()->create();
        $other = User::factory()->member()->create();
        $this->messageFor($other, 'Secret subject for another member');

        $response = $this->actingAs($member)->get(route('account.messages'));

        $response->assertOk();
        $response->assertDontSee('Secret subject for another member');
    }

    public function test_admin_cannot_access_messages_page(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->get(route('account.messages'));

        $response->assertRedirect(route('home'));
    }

    public function test_viewing_messages_marks_them_and_reply_notifications_read(): void
    {
        $member = User::factory()->member()->create();
        $message = $this->messageFor($member);

        $this->assertTrue($message->isMemberUnread());

        $member->notifications()->create([
            'id' => Str::uuid(),
            'type' => ContactMessageRepliedNotification::class,
            'data' => [
                'type' => 'contact_reply',
                'contact_message_id' => $message->id,
                'message' => 'You have a new reply.',
                'action_url' => url('/account/messages'),
            ],
        ]);

        $this->assertSame(1, $member->unreadNotifications()->count());

        $this->actingAs($member)->get(route('account.messages'))->assertOk();

        $this->assertFalse($message->fresh()->isMemberUnread());
        $this->assertSame(0, $member->unreadNotifications()->count());
    }
}
