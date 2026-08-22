<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\UpdatePasswordRequest;
use App\Http\Requests\Member\UpdateProfileRequest;
use App\Mail\PasswordUpdatedEmail;
use App\Services\AuditLogService;
use App\Services\ContributionService;
use App\Services\OnboardingService;
use App\Services\RecentlyViewedService;
use App\Services\RecommendationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function __construct(
        private readonly ContributionService $contributions,
        private readonly RecentlyViewedService $recentlyViewed,
        private readonly RecommendationService $recommendations,
        private readonly OnboardingService $onboarding,
        private readonly AuditLogService $auditLog,
    ) {}

    public function show()
    {
        $user = auth()->user();
        $stats = $this->contributions->memberStatusCounts($user->id);

        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

        return view('member.account.dashboard', [
            'stats' => $stats,
            'recent' => $this->contributions->recentMemberContributions($user->id, 5),
            'total' => array_sum($stats),
            'recentlyViewed' => $this->recentlyViewed->getRecent($user, 8),
            'recommended' => $this->recommendations->getRecommendedFor($user, 6),
            'greeting' => $greeting,
            'checklist' => $this->onboarding->checklistFor($user),
            'tourSteps' => $this->onboarding->dashboardTourSteps(),
        ]);
    }

    public function profile()
    {
        $user = auth()->user();

        return view('member.account.profile', [
            'user' => $user,
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = auth()->user();

        $user->update($request->safe()->only(['name', 'bio']));

        $this->auditLog->logWithSubject(
            action: 'profile.updated',
            subject: $user,
            changes: $user->getChanges(),
            request: $request,
        );

        return redirect()->route('account.profile')
            ->with('success', 'Your profile has been updated successfully.');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = auth()->user();
        $isFirstPassword = ! $user->hasCustomPassword();

        $user->forceFill([
            'password' => Hash::make($request->string('password')),
            'remember_token' => Str::random(60),
            'password_set_at' => now(),
        ])->save();

        Auth::logoutOtherDevices($request->string('password'));

        $this->auditLog->log(action: $isFirstPassword ? 'account.password.set' : 'account.password.changed');

        Mail::to($user->email)->send(new PasswordUpdatedEmail(
            user: $user,
            ipAddress: $request->ip(),
            created: $isFirstPassword,
        ));

        return redirect()->route('account.profile')
            ->with('success', $isFirstPassword
                ? 'Your password has been created successfully. You can now sign in with your email address too.'
                : 'Your password has been changed successfully.');
    }
}
