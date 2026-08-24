<?php

namespace Tests\Feature\Notifications;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Sop;
use App\Models\User;
use App\Notifications\MemberRegisteredNotification;
use App\Notifications\NewContactMessageNotification;
use App\Notifications\NewContributionSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function adminWithPermission(Permission $permissionModel, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge(['is_active' => true], $attributes));
        $role = Role::factory()->create();
        $role->permissions()->attach($permissionModel);
        $user->roles()->attach($role);

        return $user;
    }

    private function draftSop(User $member): Sop
    {
        return Sop::factory()->draft()->create([
            'name' => 'Specimen Handling SOP',
            'contributed_by' => $member->id,
            'created_by' => $member->id,
            'updated_by' => $member->id,
        ]);
    }

    public function test_submitting_contribution_notifies_admins_with_review_permission(): void
    {
        Notification::fake();

        $reviewer = $this->adminWithPermission(Permission::factory()->create(['slug' => 'contributions.review']));
        $plainAdmin = User::factory()->create(['is_active' => true]);
        $member = User::factory()->member()->create();
        $contribution = $this->draftSop($member);

        $this->actingAs($member)->post(
            route('account.contributions.submit', ['type' => 'sop', 'id' => $contribution->id])
        );

        Notification::assertSentTo($reviewer, NewContributionSubmittedNotification::class);
        Notification::assertNotSentTo($plainAdmin, NewContributionSubmittedNotification::class);
    }

    public function test_submission_only_notifies_active_admins(): void
    {
        Notification::fake();

        $permission = Permission::factory()->create(['slug' => 'contributions.review']);
        $activeAdmin = $this->adminWithPermission($permission);
        $inactiveAdmin = $this->adminWithPermission($permission, ['is_active' => false]);
        $member = User::factory()->member()->create();
        $contribution = $this->draftSop($member);

        $this->actingAs($member)->post(
            route('account.contributions.submit', ['type' => 'sop', 'id' => $contribution->id])
        );

        Notification::assertSentTo($activeAdmin, NewContributionSubmittedNotification::class);
        Notification::assertNotSentTo($inactiveAdmin, NewContributionSubmittedNotification::class);
    }

    public function test_admin_can_view_their_notifications(): void
    {
        $admin = User::factory()->create();
        $admin->notifications()->create([
            'id' => Str::uuid(),
            'type' => MemberRegisteredNotification::class,
            'data' => ['message' => 'Admin inbox test'],
        ]);

        $response = $this->actingAs($admin)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertSee('Admin inbox test');
    }

    public function test_notification_bell_shows_unread_count(): void
    {
        $admin = User::factory()->create();
        $admin->notifications()->create([
            'id' => Str::uuid(),
            'type' => MemberRegisteredNotification::class,
            'data' => ['message' => 'Unread bell test'],
        ]);

        $response = $this->actingAs($admin)->get(route('home'));

        $response->assertOk();
        $response->assertSee('1 unread notifications');
        $response->assertSee('/notifications/read-all');
    }

    public function test_legacy_contact_message_payload_links_to_the_admin_inbox(): void
    {
        $admin = $this->adminWithPermission(Permission::factory()->create(['slug' => 'contact-messages.view']));

        $admin->notifications()->create([
            'id' => Str::uuid(),
            'type' => NewContactMessageNotification::class,
            'data' => [
                'message' => 'New contact message from Diana Cruz',
                'action_text' => 'Open admin area',
                'action_url' => url('/admin'),
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('notifications.index'));

        $response->assertOk();
        $this->assertStringContainsString(
            'href="'.route('admin.contact-messages.index').'"',
            $response->getContent(),
        );
        $response->assertSee('Read');
    }
}
