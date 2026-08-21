<?php

namespace Tests\Feature\Notifications;

use App\Enums\ResourceStatus;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Sop;
use App\Models\User;
use App\Notifications\ContributionApprovedNotification;
use App\Notifications\ContributionRejectedNotification;
use App\Notifications\MemberRegisteredNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class MemberNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function reviewer(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'contributions.review']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        return $user;
    }

    private function pendingSop(User $contributor): Sop
    {
        return Sop::factory()->create([
            'status' => ResourceStatus::UNDER_REVIEW,
            'published_at' => null,
            'contributed_by' => $contributor->id,
            'created_by' => $contributor->id,
            'purpose' => 'Ensure accurate and reliable daily results.',
            'scope' => 'Applies to all hematology laboratory staff.',
            'procedure_content' => "## Procedure\n\nRun controls before patient samples.",
            'content' => "## Purpose\n\nEnsure accurate and reliable daily results.\n\n## Scope\n\nApplies to all hematology laboratory staff.\n\n## Procedure\n\nRun controls before patient samples.",
        ]);
    }

    public function test_registration_creates_welcome_notification(): void
    {
        $this->post(route('register.submit'), [
            'name' => 'Jane Member',
            'email' => 'jane@example.com',
            'password' => 'StrongPass1234',
            'password_confirmation' => 'StrongPass1234',
        ]);

        $user = User::where('email', 'jane@example.com')->firstOrFail();

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => MemberRegisteredNotification::class,
        ]);
    }

    public function test_registration_sends_welcome_notification(): void
    {
        Notification::fake();

        $this->post(route('register.submit'), [
            'name' => 'Jane Member',
            'email' => 'jane@example.com',
            'password' => 'StrongPass1234',
            'password_confirmation' => 'StrongPass1234',
        ]);

        $user = User::where('email', 'jane@example.com')->firstOrFail();

        Notification::assertSentTo($user, MemberRegisteredNotification::class);
    }

    public function test_contribution_approval_creates_notification_for_contributor(): void
    {
        $admin = $this->reviewer();
        $member = User::factory()->member()->create();
        $contribution = $this->pendingSop($member);

        $this->actingAs($admin)->post(
            route('admin.contributions.approve', ['type' => 'sop', 'id' => $contribution->id]),
            $this->approvePayload()
        );

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $member->id,
            'type' => ContributionApprovedNotification::class,
        ]);
    }

    public function test_contribution_rejection_creates_notification_with_reason(): void
    {
        $admin = $this->reviewer();
        $member = User::factory()->member()->create();
        $contribution = $this->pendingSop($member);

        $this->actingAs($admin)->post(
            route('admin.contributions.reject', ['type' => 'sop', 'id' => $contribution->id]),
            ['rejection_reason' => 'The procedure steps are not detailed enough to publish.']
        );

        $notification = $member->notifications()
            ->where('type', ContributionRejectedNotification::class)
            ->first();

        $this->assertNotNull($notification);
        $this->assertStringContainsString(
            'The procedure steps are not detailed enough to publish.',
            $notification->data['message']
        );
    }

    public function test_member_can_view_their_notifications(): void
    {
        $member = User::factory()->member()->create();
        $this->seedNotification($member, 'Member viewing test');

        $response = $this->actingAs($member)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertSee('Member viewing test');
    }

    public function test_member_can_mark_notification_as_read(): void
    {
        $member = User::factory()->member()->create();
        $notification = $this->seedNotification($member, 'Mark me read');

        $this->actingAs($member)->post(route('notifications.read', $notification->id));

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_member_can_mark_all_notifications_as_read(): void
    {
        $member = User::factory()->member()->create();
        $this->seedNotification($member, 'First');
        $this->seedNotification($member, 'Second');

        $this->actingAs($member)->post(route('notifications.read-all'));

        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $member->id, 'read_at' => null]);
    }

    public function test_member_cannot_mark_another_users_notification_as_read(): void
    {
        $owner = User::factory()->member()->create();
        $other = User::factory()->member()->create();
        $notification = $this->seedNotification($owner, 'Private notification');

        $response = $this->actingAs($other)->post(route('notifications.read', $notification->id));

        $response->assertForbidden();
        $this->assertDatabaseHas('notifications', ['id' => $notification->id, 'read_at' => null]);
    }

    private function seedNotification(User $user, string $message): DatabaseNotification
    {
        return $user->notifications()->create([
            'id' => Str::uuid(),
            'type' => MemberRegisteredNotification::class,
            'data' => ['message' => $message],
        ]);
    }

    private function approvePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Daily Hematology Analyzer Quality Control',
            'short_description' => 'Daily quality control procedure for the hematology analyzer.',
            'purpose' => 'Ensure accurate and reliable daily results.',
            'scope' => 'Applies to all hematology laboratory staff.',
            'procedure_content' => "## Procedure\n\nRun controls before patient samples.",
            'quality_control' => '',
            'safety_information' => '',
            'references' => '',
            'categories' => [],
            'tags' => [],
            'seo_title' => null,
            'seo_description' => null,
        ], $overrides);
    }
}
