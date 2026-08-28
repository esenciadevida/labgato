<?php

namespace Tests\Feature\Ops;

use App\Mail\WeeklyDigestEmail;
use App\Models\AnalyticsDailyMetric;
use App\Models\AuditLog;
use App\Models\KnowledgeResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WeeklyDigestTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatches_to_configured_email_when_set(): void
    {
        Mail::fake();
        config(['labgoto.security.alert_email' => 'ops@example.com']);

        $this->artisan('digest:weekly')->assertExitCode(0);

        Mail::assertQueued(WeeklyDigestEmail::class, function (WeeklyDigestEmail $mail) {
            return $mail->hasTo('ops@example.com');
        });
    }

    public function test_falls_back_to_active_administrators(): void
    {
        Mail::fake();
        config(['labgoto.security.alert_email' => '']);

        $admin = User::factory()->create(['user_type' => 'admin', 'is_active' => true]);

        $this->artisan('digest:weekly')->assertExitCode(0);

        Mail::assertQueued(WeeklyDigestEmail::class, function (WeeklyDigestEmail $mail) use ($admin) {
            return $mail->hasTo($admin->email);
        });
    }

    public function test_is_silent_when_no_recipients(): void
    {
        Mail::fake();
        config(['labgoto.security.alert_email' => '']);

        $this->artisan('digest:weekly')->assertExitCode(0);

        Mail::assertNothingQueued(WeeklyDigestEmail::class);
    }

    public function test_includes_activity_metrics(): void
    {
        Mail::fake();
        config(['labgoto.security.alert_email' => 'ops@example.com']);

        User::factory()->create(['user_type' => 'member', 'is_active' => true]);

        AnalyticsDailyMetric::create([
            'date' => now()->subDays(2)->toDateString(),
            'resource_type' => 'all',
            'status' => 'all',
            'total_count' => 500,
            'new_count' => 12,
            'search_volume' => 340,
            'search_zero_results' => 7,
            'ai_requests_total' => 40,
            'ai_requests_failed' => 2,
            'ai_tokens_used' => 99999,
        ]);

        AuditLog::factory()->create(['action' => 'login', 'created_at' => now()->subDay()]);

        DB::table('failed_jobs')->insert([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'RuntimeException: boom',
            'failed_at' => now()->subDay()->toDateTimeString(),
        ]);

        $this->artisan('digest:weekly')->assertExitCode(0);

        Mail::assertQueued(WeeklyDigestEmail::class, function (WeeklyDigestEmail $mail) {
            $metrics = $mail->metrics;

            return $metrics['Searches performed'] === '340'
                && $metrics['New members'] === '1'
                && $metrics['New resources published'] === '12'
                && $metrics['Failed background jobs'] === '1'
                && $metrics['Audit events recorded'] === '1';
        });
    }

    public function test_digest_email_view_renders(): void
    {
        $html = (new WeeklyDigestEmail(
            weekStart: '2026-01-01',
            weekEnd: '2026-01-07',
            metrics: ['New members' => '1', 'Failed background jobs' => '0'],
        ))->render();

        $this->assertStringContainsString('Weekly digest — 2026-01-01 to 2026-01-07', $html);
        $this->assertStringContainsString('New members', $html);
    }

    public function test_counts_pending_contributions(): void
    {
        Mail::fake();
        config(['labgoto.security.alert_email' => 'ops@example.com']);

        KnowledgeResource::factory()->create([
            'status' => 'under_review',
            'contributed_by' => User::factory(),
        ]);

        $this->artisan('digest:weekly', ['--days' => 1])->assertExitCode(0);

        Mail::assertQueued(WeeklyDigestEmail::class, function (WeeklyDigestEmail $mail) {
            return $mail->metrics['Contributions awaiting review'] === '1';
        });
    }
}