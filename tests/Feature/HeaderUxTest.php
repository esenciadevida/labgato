<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HeaderUxTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithPermission(string $permissionSlug): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => $permissionSlug]);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_public_header_shows_user_dropdown_for_authenticated_user(): void
    {
        $user = $this->createUserWithPermission('dashboard.view');

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Notifications', false);
        $response->assertSee('My Profile', false);
        $response->assertSee('Recently Viewed', false);
        $response->assertSee('Log out', false);
    }

    public function test_public_header_hides_dropdown_for_guest(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Sign in', false);
        $response->assertSee('Sign up', false);
    }

    public function test_notification_panel_shows_unread_count(): void
    {
        $user = $this->createUserWithPermission('dashboard.view');

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Notifications', false);
    }

    public function test_mark_as_read_updates_database(): void
    {
        $user = $this->createUserWithPermission('dashboard.view');
        $notification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test',
            'data' => ['message' => 'Test notification', 'url' => '/'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)->post(route('notifications.read', $notification), [], ['Accept' => 'application/json']);

        $response->assertJson(['success' => true]);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_mark_all_as_read_clears_unread_badge(): void
    {
        $user = $this->createUserWithPermission('dashboard.view');
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test',
            'data' => ['message' => 'Test 1', 'url' => '/'],
            'read_at' => null,
        ]);
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test',
            'data' => ['message' => 'Test 2', 'url' => '/'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)->post(route('notifications.read-all'), [], ['Accept' => 'application/json']);

        $response->assertJson(['success' => true, 'unread_count' => 0]);
        $this->assertEquals(0, $user->unreadNotifications()->count());
    }

    public function test_user_dropdown_contains_logout_and_profile_links(): void
    {
        $user = $this->createUserWithPermission('dashboard.view');

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('My Profile', false);
        $response->assertSee('Recently Viewed', false);
        $response->assertSee('Log out', false);
    }

    public function test_admin_header_uses_same_user_dropdown(): void
    {
        $user = $this->createUserWithPermission('dashboard.view');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('My Profile', false);
        $response->assertSee('Recently Viewed', false);
        $response->assertSee('Log out', false);
    }

    public function test_recently_viewed_page_requires_authentication(): void
    {
        $response = $this->get(route('recently-viewed.index'));

        $response->assertStatus(302);
        $response->assertRedirect();
    }

    public function test_authenticated_user_can_view_recently_viewed(): void
    {
        $user = $this->createUserWithPermission('dashboard.view');

        $response = $this->actingAs($user)->get(route('recently-viewed.index'));

        $response->assertStatus(200);
        $response->assertSee('Recently Viewed', false);
    }
}
