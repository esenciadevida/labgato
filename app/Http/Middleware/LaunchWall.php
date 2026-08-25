<?php

namespace App\Http\Middleware;

use App\Models\LaunchSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LaunchWall
{
    /**
     * Paths that stay reachable while the site is pre-launch.
     */
    private const ALLOWED = [
        '/',
        'login',
        'register',
        'logout',
        'launch-dashboard',
        'privacy',
        'terms',
        'disclaimer',
        'contact',
    ];

    private const ALLOWED_PREFIXES = [
        'assets/',
        'build/',
        'storage/',
        'livewire/',
        '_debugbar',
        'admin/login',
        'forgot-password',
        'reset-password/',
        'auth/google/',
        'email/',
        'verification',
        'up/',
        'legal/',
        // The scheduler (cron) endpoint must always be reachable so scheduled
        // tasks (backups, exam auto-submit, sitemap) run even pre-launch.
        'cron/',
        // Certificate verification & sharing must stay publicly reachable so
        // the QR code on a certificate always resolves, even pre-launch.
        'verify/',
        'share/',
        // The scheduler tick must be reachable even pre-launch so backups,
        // auto-submit and auto-activation still run from a Hostinger cron.
        'cron/',
        // Exam hub stays reachable pre-launch so registered members can
        // take open challenges (access still gated by challenge_open and the
        // assessment being open, plus the member middleware).
        'assessments/',
        'exam/',
        'leaderboard/',
        'certificates/',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $settings = LaunchSetting::current();

        if ($settings->isLive()) {
            return $next($request);
        }

        if (auth()->user()?->isAdmin()) {
            return $next($request);
        }

        // Request::path() normalizes the root to '/'.
        $path = $request->path();

        if (in_array($path, self::ALLOWED, true)
            || collect(self::ALLOWED_PREFIXES)->contains(fn (string $prefix) => str_starts_with($path.'/', $prefix))) {
            if ($path === 'register' && ! $settings->registration_open) {
                return redirect('/')
                    ->with('info', 'Registration opens soon. Check back at launch.');
            }

            return $next($request);
        }

        return redirect('/');
    }
}
