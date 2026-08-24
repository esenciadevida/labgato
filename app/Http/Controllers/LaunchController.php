<?php

namespace App\Http\Controllers;

use App\Http\Controllers\PublicSite\HomeController;
use App\Models\Assessment;
use App\Models\LaunchSetting;
use Illuminate\Http\Request;

class LaunchController extends Controller
{
    /**
     * Pre-launch this is the launch wall; once live it serves the normal
     * home page. The launch setting — never the browser — decides which.
     */
    public function wall()
    {
        $this->maybeActivate();

        $settings = LaunchSetting::current();

        if ($settings->isLive()) {
            return app(HomeController::class)->index();
        }

        // Logged-in members have nothing to do on the public countdown -
        // send them to their waiting room instead of looping them back here
        // after every login.
        if (auth()->user() && ! auth()->user()->isAdmin()) {
            return redirect()->route('launch.dashboard');
        }

        return view('launch.wall', [
            'settings' => $settings,
            'pageTitle' => 'Labgoto is coming',
        ]);
    }

    public function dashboard(Request $request)
    {
        $this->maybeActivate();

        $settings = LaunchSetting::current();

        if ($settings->isLive()) {
            return redirect()->route('home');
        }

        $assessments = Assessment::query()
            ->where('status', '!=', 'draft')
            ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->get();

        $states = [];

        foreach ($assessments as $assessment) {
            $attempt = $request->user()->examAttempts()
                ->where('assessment_id', $assessment->id)
                ->where('mode', 'official')
                ->orderByDesc('id')
                ->first();

            $states[$assessment->id] = [
                'attempt' => $attempt,
                'canBegin' => $settings->challenge_open
                    && $assessment->isOpen()
                    && ($attempt === null || $attempt->status === 'abandoned'),
            ];
        }

        return response()
            ->view('launch.dashboard', [
                'settings' => $settings,
                'user' => $request->user(),
                'assessments' => $assessments,
                'states' => $states,
            ])
            ->header('Cache-Control', 'private, no-store');
    }

    /**
     * Flip the main site live the moment the launch moment passes, so it does
     * not depend on the scheduler (cron). Idempotent: no-ops unless auto
     * activation is enabled, the launch time has passed, and the site is still
     * in pre-launch.
     */
    private function maybeActivate(): void
    {
        $settings = LaunchSetting::current();

        if (! $settings->auto_activate
            || $settings->launch_at === null
            || ! $settings->isPreLaunch()
            || now()->lt($settings->launch_at)) {
            return;
        }

        $settings->update(['main_site_status' => 'open']);
        LaunchSetting::flushCache();
    }
}
