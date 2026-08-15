<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\UpdateProfileRequest;
use App\Services\AuditLogService;
use App\Services\ContributionService;
use App\Services\RecentlyViewedService;
use App\Services\RecommendationService;

class MemberController extends Controller
{
    public function __construct(
        private readonly ContributionService $contributions,
        private readonly RecentlyViewedService $recentlyViewed,
        private readonly RecommendationService $recommendations,
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
}
