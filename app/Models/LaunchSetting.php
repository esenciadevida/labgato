<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LaunchSetting extends Model
{
    protected const CACHE_KEY = 'launch.settings';

    protected $fillable = [
        'launch_at',
        'timezone',
        'auto_activate',
        'registration_open',
        'challenge_open',
        'results_status',
        'answer_review_status',
        'leaderboard_status',
        'main_site_status',
    ];

    protected function casts(): array
    {
        return [
            'launch_at' => 'datetime',
            'auto_activate' => 'boolean',
            'registration_open' => 'boolean',
            'challenge_open' => 'boolean',
        ];
    }

    /**
     * Single-row control table. Cached because the launch wall middleware
     * consults it on every request.
     */
    public static function current(): self
    {
        return Cache::remember(self::CACHE_KEY, 300, fn () => static::query()->firstOrCreate([], [
            'timezone' => 'Africa/Lagos',
            'auto_activate' => true,
            'registration_open' => true,
            'challenge_open' => true,
            'results_status' => 'locked',
            'answer_review_status' => 'locked',
            'leaderboard_status' => 'locked',
            'main_site_status' => 'locked',
            'launch_at' => now()->addDays(30)->setTimezone('Africa/Lagos')->setTime(20, 0),
        ]));
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function isPreLaunch(): bool
    {
        return $this->main_site_status === 'locked';
    }

    public function isLive(): bool
    {
        return ! $this->isPreLaunch();
    }

    public function resultsAvailable(): bool
    {
        return $this->results_status === 'available';
    }

    public function answerReviewAvailable(): bool
    {
        return $this->answer_review_status === 'available';
    }

    public function leaderboardAvailable(): bool
    {
        return $this->leaderboard_status === 'available';
    }

    /**
     * Launch moment anchored to the configured timezone.
     */
    public function launchAtCarbon(): ?CarbonImmutable
    {
        if ($this->launch_at === null) {
            return null;
        }

        return CarbonImmutable::instance($this->launch_at)->setTimezone($this->timezone);
    }

    public function remainingSeconds(): int
    {
        $target = $this->launchAtCarbon();

        if ($target === null) {
            return 0;
        }

        return max(0, now()->diffInSeconds($target, false));
    }
}
