<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Popup;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PopupManagementTest extends TestCase
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
            'title' => 'Welcome to LabGato',
            'content' => 'Explore the free laboratory toolkit and reference ranges.',
            'cta_text' => 'Get started',
            'cta_url' => 'https://example.com/start',
            'trigger_type' => 'on_load',
            'delay_seconds' => 0,
            'is_active' => '1',
            'is_dismissible' => '1',
            'starts_at' => '',
            'ends_at' => '',
        ], $overrides);
    }

    public function test_super_admin_can_view_popup_index(): void
    {
        $admin = $this->superAdmin();
        Popup::factory()->create(['title' => 'Existing pop-up']);

        $response = $this->actingAs($admin)->get(route('admin.popups.index'));

        $response->assertOk();
        $response->assertSee('Pop-ups');
        $response->assertSee('Existing pop-up');
    }

    public function test_super_admin_can_view_popup_create_page(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->get(route('admin.popups.create'));

        $response->assertOk();
        $response->assertSee('Create Pop-up');
    }

    public function test_super_admin_can_create_a_popup(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->post(route('admin.popups.store'), $this->payload());

        $response->assertRedirect(route('admin.popups.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('popups', [
            'title' => 'Welcome to LabGato',
            'trigger_type' => 'on_load',
            'is_active' => true,
            'is_dismissible' => true,
            'created_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'popups.created',
            'resource_type' => Popup::class,
        ]);
    }

    public function test_super_admin_can_edit_a_popup(): void
    {
        $popup = Popup::factory()->create();
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->put(route('admin.popups.update', $popup), $this->payload([
            'title' => 'Updated pop-up',
        ]));

        $response->assertRedirect(route('admin.popups.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('popups', [
            'id' => $popup->id,
            'title' => 'Updated pop-up',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'popups.updated',
            'resource_type' => Popup::class,
        ]);
    }

    public function test_super_admin_can_delete_a_popup(): void
    {
        $popup = Popup::factory()->create();
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->delete(route('admin.popups.destroy', $popup));

        $response->assertRedirect(route('admin.popups.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('popups', ['id' => $popup->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'popups.deleted',
            'resource_type' => Popup::class,
        ]);
    }

    public function test_non_super_admin_cannot_access_popup_management(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->get(route('admin.popups.index'));

        $response->assertForbidden();
    }

    public function test_popup_requires_title_and_content(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->post(route('admin.popups.store'), $this->payload([
            'title' => '',
            'content' => '',
        ]));

        $response->assertSessionHasErrors(['title', 'content']);
        $this->assertDatabaseCount('popups', 0);
    }

    public function test_popup_rejects_invalid_trigger_type(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->post(route('admin.popups.store'), $this->payload([
            'trigger_type' => 'always',
        ]));

        $response->assertSessionHasErrors('trigger_type');
        $this->assertDatabaseCount('popups', 0);
    }

    public function test_popup_rejects_whitespace_only_title_and_content(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->post(route('admin.popups.store'), $this->payload([
            'title' => '   ',
            'content' => "\n \n",
        ]));

        $response->assertSessionHasErrors(['title', 'content']);
        $this->assertDatabaseCount('popups', 0);
    }

    public function test_popup_update_rejects_whitespace_only_title(): void
    {
        $popup = Popup::factory()->create();
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->put(route('admin.popups.update', $popup), $this->payload([
            'title' => '   ',
        ]));

        $response->assertSessionHasErrors('title');
    }
}
