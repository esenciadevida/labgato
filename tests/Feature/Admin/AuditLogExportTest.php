<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogExportTest extends TestCase
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

    public function test_admin_can_export_audit_logs_as_csv(): void
    {
        $user = $this->createUserWithPermissions(['audit-logs.view']);

        AuditLog::factory()->create([
            'action' => 'auth.login.success',
            'ip_address' => '127.0.0.1',
        ]);
        AuditLog::factory()->create(['action' => 'settings.updated']);

        $response = $this->actingAs($user)->get(route('admin.audit-logs.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('created_at,user,email,action', $content);
        $this->assertStringContainsString('auth.login.success', $content);
        $this->assertStringContainsString('settings.updated', $content);
    }

    public function test_export_respects_action_filter(): void
    {
        $user = $this->createUserWithPermissions(['audit-logs.view']);

        AuditLog::factory()->create(['action' => 'auth.login.success']);
        AuditLog::factory()->create(['action' => 'settings.updated']);

        $response = $this->actingAs($user)->get(route('admin.audit-logs.export', ['action' => 'auth.login.success']));

        $response->assertStatus(200);

        $content = $response->streamedContent();

        $this->assertStringContainsString('auth.login.success', $content);
        $this->assertStringNotContainsString('settings.updated', $content);
    }

    public function test_export_requires_audit_logs_view_permission(): void
    {
        $user = $this->createUserWithPermissions(['dashboard.view']);

        $response = $this->actingAs($user)->get(route('admin.audit-logs.export'));

        $response->assertStatus(403);
    }
}