<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AdminInvitation extends Model
{
    protected $fillable = [
        'name',
        'email',
        'role_id',
        'invited_by',
        'token',
        'expires_at',
        'accepted_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public static function issue(string $name, string $email, int $roleId, ?int $invitedBy): array
    {
        $plainToken = Str::random(48);

        $invitation = static::updateOrCreate(
            ['email' => $email, 'accepted_at' => null],
            [
                'name' => $name,
                'role_id' => $roleId,
                'invited_by' => $invitedBy,
                'token' => hash('sha256', $plainToken),
                'expires_at' => now()->addDays(7),
                'accepted_at' => null,
            ]
        );

        return [$invitation, $plainToken];
    }

    public static function findByPlainToken(string $plainToken): ?self
    {
        return static::where('token', hash('sha256', $plainToken))->first();
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null && ! $this->isExpired();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
