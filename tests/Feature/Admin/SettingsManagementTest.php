<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createSuperAdmin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create(['slug' => 'super-administrator', 'is_system_role' => true]);
        $permission = Permission::factory()->create(['slug' => 'settings.manage']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        return $user;
    }

    private function createRegularAdmin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'settings.view']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_admin_can_view_settings(): void
    {
        $user = $this->createRegularAdmin();
        Setting::factory()->create(['key' => 'general.site_name', 'value' => 'Test Site']);

        $response = $this->actingAs($user)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Settings');
    }

    public function test_admin_can_edit_non_sensitive_settings(): void
    {
        $admin = $this->createSuperAdmin();
        Setting::factory()->create(['key' => 'general.site_name', 'value' => 'Old Name']);

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'settings' => [
                'general.site_name' => 'New Name',
            ],
        ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'key' => 'general.site_name',
            'value' => 'New Name',
        ]);
    }

    public function test_sensitive_settings_are_not_editable_via_ui(): void
    {
        $admin = $this->createSuperAdmin();
        Setting::factory()->create(['key' => 'security.groq_api_key', 'value' => 'secret-key']);

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'settings' => [
                'security.groq_api_key' => 'new-secret',
            ],
        ]);

        $response->assertRedirect(route('admin.settings.index'));

        $this->assertDatabaseHas('settings', [
            'key' => 'security.groq_api_key',
            'value' => 'secret-key',
        ]);
    }

    public function test_only_super_admin_can_edit_settings(): void
    {
        $user = $this->createRegularAdmin();

        $response = $this->actingAs($user)->put(route('admin.settings.update'), [
            'settings' => [
                'general.site_name' => 'Hacked',
            ],
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthorized_user_cannot_access_settings(): void
    {
        $user = $this->createRegularAdmin();

        $response = $this->actingAs($user)->get(route('admin.settings.index'));

        $response->assertStatus(200);
    }

    public function test_user_without_settings_view_permission_is_blocked(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => 'dashboard.view']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get(route('admin.settings.index'));

        $response->assertStatus(403);
    }
}
