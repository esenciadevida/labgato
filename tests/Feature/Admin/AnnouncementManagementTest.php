<?php

namespace Tests\Feature\Admin;

use App\Models\Announcement;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementManagementTest extends TestCase
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

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'message' => 'Scheduled maintenance on Saturday at 8 PM.',
            'type' => 'warning',
            'link_text' => 'View details',
            'link_url' => 'https://example.com/status',
            'is_active' => '1',
            'is_dismissible' => '1',
            'starts_at' => '',
            'ends_at' => '',
        ], $overrides);
    }

    public function test_super_admin_can_view_announcement_index(): void
    {
        $admin = $this->superAdmin();
        Announcement::factory()->create(['message' => 'Existing announcement']);

        $response = $this->actingAs($admin)->get(route('admin.announcements.index'));

        $response->assertOk();
        $response->assertSee('Announcements');
        $response->assertSee('Existing announcement');
    }

    public function test_super_admin_can_create_an_announcement(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->post(route('admin.announcements.store'), $this->payload());

        $response->assertRedirect(route('admin.announcements.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('announcements', [
            'message' => 'Scheduled maintenance on Saturday at 8 PM.',
            'type' => 'warning',
            'is_active' => true,
            'is_dismissible' => true,
            'created_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'announcements.created',
            'resource_type' => Announcement::class,
        ]);
    }

    public function test_super_admin_can_edit_an_announcement(): void
    {
        $announcement = Announcement::factory()->create();
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->put(route('admin.announcements.update', $announcement), $this->payload([
            'message' => 'Updated message',
        ]));

        $response->assertRedirect(route('admin.announcements.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'message' => 'Updated message',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'announcements.updated',
            'resource_type' => Announcement::class,
        ]);
    }

    public function test_super_admin_can_delete_an_announcement(): void
    {
        $announcement = Announcement::factory()->create();
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->delete(route('admin.announcements.destroy', $announcement));

        $response->assertRedirect(route('admin.announcements.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'announcements.deleted',
            'resource_type' => Announcement::class,
        ]);
    }

    public function test_non_super_admin_cannot_access_announcement_management(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->get(route('admin.announcements.index'));

        $response->assertForbidden();
    }

    public function test_announcement_requires_message(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->post(route('admin.announcements.store'), $this->payload([
            'message' => '',
        ]));

        $response->assertSessionHasErrors('message');
        $this->assertDatabaseCount('announcements', 0);
    }

    public function test_announcement_type_validation(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->post(route('admin.announcements.store'), $this->payload([
            'type' => 'urgent',
        ]));

        $response->assertSessionHasErrors('type');
        $this->assertDatabaseCount('announcements', 0);
    }
}
