<?php

namespace Tests\Feature\Ops;

use App\Mail\AdminErrorAlert;
use App\Support\ExceptionNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class ExceptionNotifierTest extends TestCase
{
    use RefreshDatabase;

    public function test_does_not_notify_outside_production(): void
    {
        Mail::fake();

        ExceptionNotifier::notify(new RuntimeException('boom'), 'testing');

        Mail::assertNothingQueued(AdminErrorAlert::class);
    }

    public function test_returns_without_action_when_no_alert_email_configured(): void
    {
        Mail::fake();
        config(['labgoto.security.alert_email' => '']);

        ExceptionNotifier::notify(new RuntimeException('boom'), 'production');

        Mail::assertNothingQueued(AdminErrorAlert::class);
    }

    public function test_queues_alert_for_server_errors_in_production(): void
    {
        Mail::fake();
        config(['labgoto.security.alert_email' => 'admin@example.com']);
        config(['labgoto.security.alert_cooldown_seconds' => 900]);

        ExceptionNotifier::notify(new RuntimeException('boom'), 'production');

        Mail::assertQueued(AdminErrorAlert::class, function (AdminErrorAlert $mail) {
            return $mail->hasTo('admin@example.com') && str_contains($mail->exceptionMessage, 'boom');
        });
    }

    public function test_deduplicates_same_error_within_cooldown(): void
    {
        Mail::fake();
        config(['labgoto.security.alert_email' => 'admin@example.com']);
        config(['labgoto.security.alert_cooldown_seconds' => 900]);

        $exception = new RuntimeException('boom');

        ExceptionNotifier::notify($exception, 'production');
        ExceptionNotifier::notify($exception, 'production');

        Mail::assertQueued(AdminErrorAlert::class, 1);
    }

    public function test_skips_client_side_errors(): void
    {
        Mail::fake();
        config(['labgoto.security.alert_email' => 'admin@example.com']);

        ExceptionNotifier::notify(new NotFoundHttpException(), 'production');

        Mail::assertNothingQueued(AdminErrorAlert::class);
    }

    public function test_error_alert_email_view_renders(): void
    {
        $html = (new AdminErrorAlert(
            exceptionClass: RuntimeException::class,
            exceptionMessage: 'boom',
            file: '/app/SomeFile.php',
            line: 12,
            url: 'https://www.labgoto.com/knowledge',
            method: 'GET',
        ))->render();

        $this->assertStringContainsString('SomeFile.php', $html);
        $this->assertStringContainsString('boom', $html);
    }

    public function test_cooldown_can_be_disabled(): void
    {
        Mail::fake();
        config(['labgoto.security.alert_email' => 'admin@example.com']);
        config(['labgoto.security.alert_cooldown_seconds' => 0]);

        ExceptionNotifier::notify(new RuntimeException('boom'), 'production');
        ExceptionNotifier::notify(new RuntimeException('boom'), 'production');

        Mail::assertQueued(AdminErrorAlert::class, 2);
    }
}