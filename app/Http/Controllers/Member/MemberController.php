<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\UpdatePasswordRequest;
use App\Http\Requests\Member\UpdateProfileRequest;
use App\Mail\ConfirmPasswordCreationEmail;
use App\Mail\PasswordUpdatedEmail;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\ContributionService;
use App\Services\ContentImageService;
use App\Services\LeaderboardService;
use App\Services\OnboardingService;
use App\Services\RecentlyViewedService;
use App\Services\RecommendationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    private const PASSWORD_CONFIRM_MINUTES = 30;

    public function __construct(
        private readonly ContributionService $contributions,
        private readonly RecentlyViewedService $recentlyViewed,
        private readonly RecommendationService $recommendations,
        private readonly OnboardingService $onboarding,
        private readonly AuditLogService $auditLog,
        private readonly ContentImageService $imageService,
        private readonly LeaderboardService $leaderboard,
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

        $profileData = $request->safe()->except(['name', 'bio', 'photo', 'remove_photo']);

        foreach (['show_country', 'show_institution', 'show_on_leaderboard'] as $flag) {
            $profileData[$flag] = $request->boolean($flag);
        }

        $existingPhotoPath = $user->profile()->value('photo_path');

        if ($request->hasFile('photo')) {
            if ($existingPhotoPath) {
                Storage::disk('public')->delete($existingPhotoPath);
            }
            $profileData['photo_path'] = $this->imageService->process($request->file('photo'), 'profiles', true)['path'];
        } elseif ($request->boolean('remove_photo') && $existingPhotoPath) {
            Storage::disk('public')->delete($existingPhotoPath);
            $profileData['photo_path'] = null;
        }

        $profile = $user->saveProfile($profileData);

        $profile->forceFill([
            'profile_completed' => $profile->completionPercent() >= 100,
        ])->save();

        $this->leaderboard->flushAllOther();

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

        if (! $user->hasCustomPassword()) {
            return $this->stagePasswordCreation($request, $user);
        }

        $user->forceFill([
            'password' => Hash::make($request->string('password')),
            'remember_token' => Hash::make(Str::random(60)),
            'password_set_at' => now(),
        ])->save();

        Auth::logoutOtherDevices($request->string('password'));

        $this->auditLog->log(action: 'account.password.changed');

        Mail::to($user->email)->queue(new PasswordUpdatedEmail(
            user: $user,
            ipAddress: $request->ip(),
            created: false,
        ));

        return redirect()->route('account.profile')
            ->with('success', 'Your password has been changed successfully.');
    }

    public function createPasswordConfirm(Request $request): RedirectResponse
    {
        $token = (string) $request->route('token');
        $payload = Cache::pull("password-creation:{$token}");

        abort_if($payload === null, 419, 'This confirmation link has expired or has already been used.');
        abort_if((int) ($payload['user_id'] ?? 0) !== $request->user()->id, 403, 'This confirmation link belongs to another account.');

        $user = User::find($payload['user_id']);

        abort_if($user === null, 404, 'Account not found.');

        $user->forceFill([
            'password' => $payload['hash'],
            'remember_token' => Hash::make(Str::random(60)),
            'password_set_at' => now(),
        ])->save();

        $this->auditLog->log(action: 'account.password.set');

        Mail::to($user->email)->queue(new PasswordUpdatedEmail(
            user: $user,
            ipAddress: $request->ip(),
            created: true,
        ));

        return redirect()->route('account.profile')
            ->with('success', 'Your password has been created successfully. You can now sign in with your email address too.');
    }

    private function stagePasswordCreation(Request $request, User $user): RedirectResponse
    {
        $token = Str::random(64);

        Cache::put(
            "password-creation:{$token}",
            [
                'user_id' => $user->id,
                'hash' => Hash::make($request->string('password')),
            ],
            now()->addMinutes(self::PASSWORD_CONFIRM_MINUTES),
        );

        $confirmationUrl = URL::temporarySignedRoute(
            'account.password.create-confirm',
            now()->addMinutes(self::PASSWORD_CONFIRM_MINUTES),
            ['token' => $token],
        );

        Mail::to($user->email)->queue(new ConfirmPasswordCreationEmail(
            user: $user,
            confirmationUrl: $confirmationUrl,
            ipAddress: $request->ip(),
            expireMinutes: self::PASSWORD_CONFIRM_MINUTES,
        ));

        return redirect()->route('account.profile')
            ->with('success', 'Almost done! Check your email for a confirmation link to finish creating your password. The link expires in '.self::PASSWORD_CONFIRM_MINUTES.' minutes.');
    }
}
