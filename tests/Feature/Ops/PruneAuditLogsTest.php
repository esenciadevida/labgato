<?php

namespace Tests\Feature\Ops;

use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneAuditLogsTest extends TestCase
{
    use RefreshDatabase;

    public function test_prunes_logs_older_than_retention(): void
    {
        $old = AuditLog::factory()->create(['created_at' => now()->subDays(90)]);
        $recent = AuditLog::factory()->create(['created_at' => now()->subDay()]);

        $this->artisan('audit-logs:prune', ['--days' => 30])
            ->expectsOutputToContain('Pruned 1')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('audit_logs', ['id' => $old->id]);
        $this->assertDatabaseHas('audit_logs', ['id' => $recent->id]);
    }

    public function test_uses_config_retention_by_default(): void
    {
        config(['labgoto.security.audit_retention_days' => 60]);

        AuditLog::factory()->create(['created_at' => now()->subDays(90)]);
        $recent = AuditLog::factory()->create(['created_at' => now()->subDays(30)]);

        $this->artisan('audit-logs:prune')->assertExitCode(0);

        $this->assertDatabaseHas('audit_logs', ['id' => $recent->id]);
        $this->assertSame(1, AuditLog::count());
    }

    public function test_rejects_invalid_retention(): void
    {
        $this->artisan('audit-logs:prune', ['--days' => 2])
            ->assertExitCode(1);
    }
}