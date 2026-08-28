<?php

namespace Tests\Feature\Ops;

use App\Mail\QueueHealthAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MonitorQueueHealthTest extends TestCase
{
    use RefreshDatabase;

    private function failedJob(string $failedAt): void
    {
        DB::table('failed_jobs')->insert([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'RuntimeException: boom',
            'failed_at' => $failedAt,
        ]);
    }

    public function test_alerts_when_failed_jobs_exceed_threshold(): void
    {
        Mail::fake();
        config(['labgoto.security.alert_email' => 'admin@example.com']);
        config(['labgoto.security.alert_cooldown_seconds' => 900]);

        $this->failedJob(now()->subHours(2)->toDateTimeString());
        $this->failedJob(now()->subHours(4)->toDateTimeString());

        $this->artisan('queue:health', ['--window' => 24, '--failed-threshold' => 2])
            ->assertExitCode(0);

        Mail::assertQueued(QueueHealthAlert::class, function (QueueHealthAlert $mail) {
            return $mail->hasTo('admin@example.com') && $mail->failedCount === 2;
        });
    }

    public function test_silent_when_below_threshold(): void
    {
        Mail::fake();
        config(['labgoto.security.alert_email' => 'admin@example.com']);

        $this->failedJob(now()->subHours(2)->toDateTimeString());

        $this->artisan('queue:health', ['--window' => 24, '--failed-threshold' => 5])
            ->assertExitCode(0);

        Mail::assertNothingQueued(QueueHealthAlert::class);
    }

    public function test_only_counts_failed_at_within_window(): void
    {
        Mail::fake();
        config(['labgoto.security.alert_email' => 'admin@example.com']);

        $this->failedJob(now()->subDays(10)->toDateTimeString());

        $this->artisan('queue:health', ['--window' => 24, '--failed-threshold' => 1])
            ->assertExitCode(0);

        Mail::assertNothingQueued(QueueHealthAlert::class);
    }

    public function test_alerts_at_most_once_per_day(): void
    {
        Mail::fake();
        config(['labgoto.security.alert_email' => 'admin@example.com']);

        $this->failedJob(now()->subHour()->toDateTimeString());

        $this->artisan('queue:health', ['--window' => 24, '--failed-threshold' => 1]);
        $this->artisan('queue:health', ['--window' => 24, '--failed-threshold' => 1]);

        Mail::assertQueued(QueueHealthAlert::class, 1);
    }

    public function test_alert_email_view_renders(): void
    {
        $html = (new QueueHealthAlert(failedCount: 5, windowHours: 24))->render();

        $this->assertStringContainsString('queued jobs have failed in the last 24 hours', $html);
    }

    public function test_skips_when_no_alert_email(): void
    {
        Mail::fake();
        config(['labgoto.security.alert_email' => '']);

        $this->failedJob(now()->subHour()->toDateTimeString());

        $this->artisan('queue:health', ['--window' => 24, '--failed-threshold' => 1])
            ->assertExitCode(0);

        Mail::assertNothingQueued(QueueHealthAlert::class);
    }
}