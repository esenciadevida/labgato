<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\ExamAttempt;
use App\Models\LaunchSetting;
use Illuminate\Support\Facades\Cache;

class LeaderboardService
{
    public const PER_PAGE = 50;

    private const CACHE_TTL = 300;

    /**
     * Global launch setting unlocks every board; a per-assessment override
     * can reveal an individual board early.
     */
    public function isBoardVisible(Assessment $assessment): bool
    {
        return LaunchSetting::current()->leaderboardAvailable()
            || ! $assessment->isLockedLeaderboard();
    }

    /**
     * Ranked official attempts for one assessment, cached for five minutes.
     *
     * Ordering rule (single source of truth):
     * score DESC, then time_spent_seconds ASC, then submitted_at ASC.
     * Rank is the sequential position after that ordering.
     *
     * @return array<int, array{user_id: int, name: string, photo_url: ?string, score: int, percent: float, time_spent: ?int, rank: int, anonymous: bool}>
     */
    public function board(Assessment $assessment): array
    {
        return Cache::remember(
            "leaderboard:{$assessment->id}",
            self::CACHE_TTL,
            function () use ($assessment) {
                return ExamAttempt::query()
                    ->where('assessment_id', $assessment->id)
                    ->where('mode', 'official')
                    ->whereIn('status', ['submitted', 'auto_submitted'])
                    ->whereNotNull('score')
                    ->with('user.profile')
                    ->orderByDesc('score')
                    ->orderBy('time_spent_seconds')
                    ->orderBy('submitted_at')
                    ->get()
                    ->values()
                    ->map(fn (ExamAttempt $attempt, int $index) => [
                        'user_id' => $attempt->user_id,
                        'name' => $this->displayName($attempt),
                        'photo_url' => $attempt->user?->profile?->photo_url,
                        'score' => (int) $attempt->score,
                        'percent' => (float) $attempt->percentage,
                        'time_spent' => $attempt->time_spent_seconds !== null ? (int) $attempt->time_spent_seconds : null,
                        'rank' => $index + 1,
                        'anonymous' => $this->isAnonymous($attempt),
                    ])
                    ->all();
            }
        );
    }

    /**
     * All-time aggregate board, cached for five minutes.
     *
     * Per assessment only the best attempt counts; the score is the rounded
     * average of those best percentages and entries is how many distinct
     * assessments the member completed. Ordered score DESC, entries DESC.
     *
     * @return array<int, array{user_id: int, name: string, photo_url: ?string, score: int, entries: int, rank: int, anonymous: bool}>
     */
    public function allTime(): array
    {
        return Cache::remember('leaderboard:alltime', self::CACHE_TTL, function () {
            $bests = ExamAttempt::query()
                ->where('mode', 'official')
                ->whereIn('status', ['submitted', 'auto_submitted'])
                ->whereNotNull('percentage')
                ->with('user.profile')
                ->get()
                ->groupBy(fn (ExamAttempt $attempt) => $attempt->user_id.':'.$attempt->assessment_id)
                ->map(fn ($group) => $group->sortByDesc('percentage')->first())
                ->groupBy('user_id');

            $rows = $bests
                ->map(function ($perAssessment) {
                    $first = $perAssessment->first();

                    return [
                        'user_id' => (int) $first->user_id,
                        'name' => $this->displayName($first),
                        'photo_url' => $first->user?->profile?->photo_url,
                        'score' => (int) round((float) $perAssessment->avg('percentage')),
                        'entries' => $perAssessment->count(),
                        'anonymous' => $this->isAnonymous($first),
                    ];
                })
                ->sortBy(function (array $row) {
                    // score DESC, then entries DESC.
                    return [-$row['score'], -$row['entries']];
                })
                ->values();

            return $rows
                ->map(function (array $row, int $index) {
                    $row['rank'] = $index + 1;

                    return $row;
                })
                ->all();
        });
    }

    /**
     * The signed-in member's rank within a board, or null when absent.
     */
    public function position(array $board, ?int $userId): ?int
    {
        if ($userId === null) {
            return null;
        }

        foreach ($board as $row) {
            if ((int) $row['user_id'] === $userId) {
                return (int) $row['rank'];
            }
        }

        return null;
    }

    public function pageFor(int $position, int $perPage = self::PER_PAGE): int
    {
        return intdiv(max(1, $position) - 1, max(1, $perPage)) + 1;
    }

    /**
     * Drop cached boards after scores change.
     */
    public function flush(?Assessment $assessment = null): void
    {
        if ($assessment !== null) {
            Cache::forget("leaderboard:{$assessment->id}");
        }

        Cache::forget('leaderboard:alltime');
    }

    private function displayName(ExamAttempt $attempt): string
    {
        $profile = $attempt->user?->profile;

        if ($this->isAnonymous($attempt)) {
            return 'Anonymous';
        }

        if ($profile !== null && filled($profile->display_name)) {
            return $profile->display_name;
        }

        return $attempt->user?->name ?? 'Anonymous';
    }

    private function isAnonymous(ExamAttempt $attempt): bool
    {
        $profile = $attempt->user?->profile;

        return $profile !== null && $profile->show_on_leaderboard === false;
    }
}
