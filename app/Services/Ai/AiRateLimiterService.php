<?php

namespace App\Services\Ai;

use App\Exceptions\Ai\AiRateLimitException;
use App\Models\AiUsage;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

class AiRateLimiterService
{
    public function assertWithinLimits(?User $user): void
    {
        $this->assertCooldown($user);
        $this->assertUserDailyQuota($user);
        $this->assertGlobalDailyQuota();
    }

    public function hitCooldown(?User $user): void
    {
        RateLimiter::hit($this->cooldownKey($user), $this->cooldownSeconds());
    }

    public function cooldownSeconds(): int
    {
        return max(0, (int) config('labgoto.ai.limits.cooldown_seconds', 5));
    }

    public function dailyUserLimit(): int
    {
        return max(1, (int) config('labgoto.ai.limits.daily_user', 50));
    }

    public function dailyGlobalLimit(): int
    {
        return max(1, (int) config('labgoto.ai.limits.daily_global', 1000));
    }

    private function assertCooldown(?User $user): void
    {
        if (RateLimiter::tooManyAttempts($this->cooldownKey($user), 1)) {
            throw new AiRateLimitException('You must wait a few seconds before generating again.');
        }
    }

    private function assertUserDailyQuota(?User $user): void
    {
        if ($user && $this->userGenerationsToday($user) >= $this->dailyUserLimit()) {
            throw new AiRateLimitException('You have reached your AI usage limit for today. Please try again tomorrow.');
        }
    }

    private function assertGlobalDailyQuota(): void
    {
        if ($this->globalGenerationsToday() >= $this->dailyGlobalLimit()) {
            throw new AiRateLimitException('The platform AI usage limit for today has been reached.');
        }
    }

    private function userGenerationsToday(User $user): int
    {
        return AiUsage::query()
            ->where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->count();
    }

    private function globalGenerationsToday(): int
    {
        return AiUsage::query()
            ->whereDate('created_at', today())
            ->count();
    }

    private function cooldownKey(?User $user): string
    {
        return 'ai:cooldown:'.($user?->id ?? 'guest');
    }
}
