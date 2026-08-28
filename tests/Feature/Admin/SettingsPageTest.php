<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase;

    private function createSuperAdmin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create(['slug' => 'super-administrator', 'is_system_role' => true]);
        $role->permissions()->attach([
            Permission::factory()->create(['slug' => 'settings.view']),
            Permission::factory()->create(['slug' => 'settings.manage']),
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    private function createSetting(string $key, string $value, string $type, string $group, string $label, ?string $description = null): Setting
    {
        return Setting::factory()->create([
            'key' => $key,
            'value' => $value,
            'type' => $type,
            'group' => $group,
            'label' => $label,
            'description' => $description,
        ]);
    }

    public function test_settings_page_is_accessible_to_super_admin(): void
    {
        $response = $this->actingAs($this->createSuperAdmin())
            ->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Settings');
    }

    public function test_settings_page_displays_grouped_settings(): void
    {
        $admin = $this->createSuperAdmin();
        $this->createSetting('general.site_name', 'Labgoto', 'string', 'general', 'Site Name');
        $this->createSetting('features.public_registration', 'true', 'boolean', 'features', 'Enable Public Registration');
        $this->createSetting('seo.default_title', 'Labgoto — Professional Laboratory Toolkit', 'string', 'seo', 'Default SEO Title');

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('General');
        $response->assertSee('Features');
        $response->assertSee('SEO');
        $response->assertSee('Site Name');
        $response->assertSee('Enable Public Registration');
        $response->assertSee('Default SEO Title');
    }

    public function test_general_group_shows_site_name(): void
    {
        $admin = $this->createSuperAdmin();
        $this->createSetting('general.site_name', 'Labgoto', 'string', 'general', 'Site Name', 'The name displayed across the platform.');

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Site Name');
        $response->assertSee('name="settings[general.site_name]"', false);
        $response->assertSee('value="Labgoto"', false);
        $response->assertSee('The name displayed across the platform.');
    }

    public function test_features_group_shows_feature_toggles(): void
    {
        $admin = $this->createSuperAdmin();
        $this->createSetting('features.contributions', 'true', 'boolean', 'features', 'Enable Community Contributions');
        $this->createSetting('features.public_registration', 'false', 'boolean', 'features', 'Enable Public Registration');

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Enable Community Contributions');
        $response->assertSee('Enable Public Registration');
        $response->assertSee('name="settings[features.contributions]"', false);
        $response->assertSee('name="settings[features.public_registration]"', false);
    }

    public function test_sensitive_values_are_not_displayed(): void
    {
        $admin = $this->createSuperAdmin();
        $this->createSetting('ai.groq_api_key', 'super-secret-key', 'string', 'ai', 'Groq API Key');

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertDontSee('super-secret-key');
        $response->assertDontSee('Groq API Key');
    }

    public function test_updating_a_setting_persists_the_change(): void
    {
        $admin = $this->createSuperAdmin();
        $this->createSetting('general.site_name', 'Labgoto', 'string', 'general', 'Site Name');

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'settings' => [
                'general.site_name' => 'Labgoto',
            ],
        ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('settings', [
            'key' => 'general.site_name',
            'value' => 'Labgoto',
        ]);
    }

    public function test_non_super_admin_without_permission_cannot_access_settings(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();
        $role->permissions()->attach(Permission::factory()->create(['slug' => 'dashboard.view']));
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get(route('admin.settings.index'));

        $response->assertStatus(403);
    }
}
