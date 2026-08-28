<?php

namespace App\Services;

use App\Mail\TwoFactorLoginEmail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminTwoFactorService
{
    public const TTL_SECONDS = 600;
    public const MAX_ATTEMPTS = 5;

    public function enabled(): bool
    {
        return (bool) config('labgoto.security.two_factor_admin', false);
    }

    public function begin(User $user): string
    {
        $token = Str::random(64);
        $code = $this->randomCode();

        Mail::to($user->email)->queue(new TwoFactorLoginEmail($user, $code));

        $this->store($token, $user->id, Hash::make($code));

        return $token;
    }

    public function resend(string $token): bool
    {
        $pending = $this->pending($token);

        if ($pending === null) {
            return false;
        }

        $user = User::find($pending['user_id']);

        if ($user === null) {
            return false;
        }

        $code = $this->randomCode();

        Mail::to($user->email)->queue(new TwoFactorLoginEmail($user, $code));

        $this->store($token, $user->id, Hash::make($code));

        return true;
    }

    public function verify(string $token, string $code): ?User
    {
        $pending = $this->pending($token);

        if ($pending === null) {
            return null;
        }

        $attempts = (int) ($pending['attempts'] ?? 0);

        if ($attempts >= self::MAX_ATTEMPTS || ! Hash::check($code, $pending['code'])) {
            $this->store($token, (int) $pending['user_id'], $pending['code'], $attempts + 1);

            return null;
        }

        Cache::forget($this->cacheKey($token));

        return User::find($pending['user_id']);
    }

    public function pending(string $token): ?array
    {
        $pending = Cache::get($this->cacheKey($token));

        return is_array($pending) ? $pending : null;
    }

    private function store(string $token, int $userId, string $codeHash, int $attempts = 0): void
    {
        Cache::put(
            $this->cacheKey($token),
            [
                'user_id' => $userId,
                'code' => $codeHash,
                'attempts' => $attempts,
            ],
            now()->addSeconds(self::TTL_SECONDS),
        );
    }

    private function cacheKey(string $token): string
    {
        return "2fa:pending:{$token}";
    }

    private function randomCode(): string
    {
        return (string) random_int(100000, 999999);
    }
}