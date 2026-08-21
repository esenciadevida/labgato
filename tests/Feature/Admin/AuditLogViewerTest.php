<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogViewerTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithPermissions(array $permissionSlugs): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();

        foreach ($permissionSlugs as $slug) {
            $permission = Permission::factory()->create(['slug' => $slug]);
            $role->permissions()->attach($permission);
        }

        $user->roles()->attach($role);

        return $user;
    }

    public function test_admin_can_view_audit_logs_with_permission(): void
    {
        $user = $this->createUserWithPermissions(['audit-logs.view']);

        $response = $this->actingAs($user)->get(route('admin.audit-logs.index'));

        $response->assertStatus(200);
        $response->assertSee('Audit Logs');
    }

    public function test_audit_logs_are_read_only(): void
    {
        $user = $this->createUserWithPermissions(['audit-logs.view']);
        $log = AuditLog::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.audit-logs.index'));

        $response->assertStatus(200);
        $response->assertDontSee('>Edit<', false);
        $response->assertDontSee('>Delete<', false);
    }

    public function test_audit_logs_show_masked_sensitive_fields(): void
    {
        $user = $this->createUserWithPermissions(['audit-logs.view']);
        $log = AuditLog::factory()->create([
            'changes' => [
                'password' => ['old' => 'oldpass', 'new' => 'newpass'],
                'name' => ['old' => 'Old Name', 'new' => 'New Name'],
            ],
        ]);

        $response = $this->actingAs($user)->get(route('admin.audit-logs.show', $log));

        $response->assertStatus(200);
        $response->assertSee('***');
        $response->assertSee('New Name');
        $response->assertDontSee('oldpass');
        $response->assertDontSee('newpass');
    }

    public function test_audit_log_filtering_works(): void
    {
        $user = $this->createUserWithPermissions(['audit-logs.view']);
        AuditLog::factory()->create(['action' => 'created']);
        AuditLog::factory()->create(['action' => 'updated']);

        $response = $this->actingAs($user)->get(route('admin.audit-logs.index', ['action' => 'created']));

        $response->assertStatus(200);
        $response->assertSee('Created');
    }

    public function test_audit_log_actions_are_displayed_formatted(): void
    {
        $user = $this->createUserWithPermissions(['audit-logs.view']);
        $log = AuditLog::factory()->create(['action' => 'auth.login.success']);

        $index = $this->actingAs($user)->get(route('admin.audit-logs.index'));

        $index->assertStatus(200);
        $index->assertSee('Auth · Login · Success');

        $show = $this->actingAs($user)->get(route('admin.audit-logs.show', $log));

        $show->assertStatus(200);
        $show->assertSee('Auth · Login · Success');
    }

    public function test_unauthorized_user_cannot_access_audit_logs(): void
    {
        $user = $this->createUserWithPermissions(['dashboard.view']);

        $response = $this->actingAs($user)->get(route('admin.audit-logs.index'));

        $response->assertStatus(403);
    }
}
