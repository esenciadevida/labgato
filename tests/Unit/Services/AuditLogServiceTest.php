<?php

namespace Tests\Unit\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_service_creates_entry(): void
    {
        $service = app(AuditLogService::class);

        $service->log(
            action: 'test.action',
            resourceType: 'App\\Models\\User',
            resourceId: 1,
            changes: ['name' => 'Test User']
        );

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'test.action',
            'resource_type' => 'App\\Models\\User',
            'resource_id' => 1,
        ]);
    }

    public function test_audit_log_service_strips_sensitive_fields(): void
    {
        $service = app(AuditLogService::class);

        $service->log(
            action: 'test.action',
            changes: [
                'name' => 'Test User',
                'password' => 'secret123',
                'api_key' => 'abc123',
            ]
        );

        $log = AuditLog::latest()->first();
        $changes = $log->changes;

        $this->assertArrayHasKey('name', $changes);
        $this->assertArrayNotHasKey('password', $changes);
        $this->assertArrayNotHasKey('api_key', $changes);
    }

    public function test_audit_log_service_captures_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $service = app(AuditLogService::class);
        $service->log(action: 'test.action');

        $log = AuditLog::latest()->first();
        $this->assertEquals($user->id, $log->user_id);
    }

    public function test_audit_log_service_handles_null_user_gracefully(): void
    {
        $service = app(AuditLogService::class);

        $service->log(action: 'test.action');

        $log = AuditLog::latest()->first();
        $this->assertNull($log->user_id);
    }
}
