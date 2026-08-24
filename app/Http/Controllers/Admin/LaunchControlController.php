<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\LaunchSetting;
use App\Services\ResultsReleaseNotifier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LaunchControlController extends Controller
{
    public function index()
    {
        return view('admin.launch.index', [
            'settings' => LaunchSetting::current(),
            'timezones' => $this->timezoneOptions(),
        ]);
    }

    public function update(Request $request)
    {
        if (! $request->user()?->isSuperAdministrator()) {
            abort(403);
        }

        $validated = $request->validate([
            'launch_date' => ['required', 'date'],
            'launch_time' => ['required', 'date_format:H:i'],
            'timezone' => ['required', 'timezone:all'],
            'auto_activate' => ['required', 'boolean'],
            'registration_open' => ['required', 'boolean'],
            'challenge_open' => ['required', 'boolean'],
            'results_status' => ['required', Rule::in(['locked', 'available'])],
            'answer_review_status' => ['required', Rule::in(['locked', 'available'])],
            'leaderboard_status' => ['required', Rule::in(['locked', 'available'])],
            'main_site_status' => ['required', Rule::in(['locked', 'open'])],
        ]);

        $previousResultsStatus = LaunchSetting::current()->results_status;

        LaunchSetting::current()->update([
            'launch_at' => \Carbon\Carbon::parse(
                $validated['launch_date'].' '.$validated['launch_time'],
                $validated['timezone']
            )->setTimezone(config('app.timezone')),
            'timezone' => $validated['timezone'],
            'auto_activate' => $validated['auto_activate'],
            'registration_open' => $validated['registration_open'],
            'challenge_open' => $validated['challenge_open'],
            'results_status' => $validated['results_status'],
            'answer_review_status' => $validated['answer_review_status'],
            'leaderboard_status' => $validated['leaderboard_status'],
            'main_site_status' => $validated['main_site_status'],
        ]);

        LaunchSetting::flushCache();

        if ($previousResultsStatus === 'locked' && $validated['results_status'] === 'available') {
            $this->notifyReleasedAssessments();
        }

        return redirect()->route('admin.launch')
            ->with('success', 'Launch settings updated.');
    }

    /**
     * Global results just unlocked; every assessment already flagged
     * "available" becomes visible to its participants. The notifier is
     * idempotent, so assessments without participants are no-ops.
     */
    private function notifyReleasedAssessments(): void
    {
        foreach (Assessment::query()->where('results_status', 'available')->get() as $assessment) {
            app(ResultsReleaseNotifier::class)->notifyReleased($assessment);
        }
    }

    private function timezoneOptions(): array
    {
        return [
            'Africa/Lagos',
            'Africa/Accra',
            'Africa/Nairobi',
            'Africa/Cairo',
            'Africa/Johannesburg',
            'Europe/London',
            'Europe/Paris',
            'America/New_York',
            'America/Chicago',
            'America/Los_Angeles',
            'Asia/Dubai',
            'Asia/Karachi',
            'Asia/Kolkata',
            'Asia/Manila',
            'Australia/Sydney',
            'UTC',
        ];
    }
}
