<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ExamAttempt extends Model
{
    public const MODES = ['official', 'practice'];

    public const STATUSES = ['in_progress', 'submitted', 'auto_submitted', 'abandoned'];

    public const INTEGRITY_STATUSES = ['normal', 'review', 'high_risk'];

    protected $fillable = [
        'user_id',
        'assessment_id',
        'attempt_number',
        'mode',
        'status',
        'question_ids',
        'option_orders',
        'started_at',
        'ends_at',
        'submitted_at',
        'score',
        'percentage',
        'time_spent_seconds',
        'integrity_status',
    ];

    protected function casts(): array
    {
        return [
            'question_ids' => 'array',
            'option_orders' => 'array',
            'started_at' => 'datetime',
            'ends_at' => 'datetime',
            'submitted_at' => 'datetime',
            'percentage' => 'decimal:2',
            'integrity_status' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class);
    }

    public function integrityEvents(): HasMany
    {
        return $this->hasMany(IntegrityEvent::class);
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }

    public function isExpired(): bool
    {
        return $this->status === 'in_progress'
            && $this->ends_at !== null
            && now()->gt($this->ends_at);
    }

    public function isOfficial(): bool
    {
        return $this->mode === 'official';
    }

    public function isPractice(): bool
    {
        return $this->mode === 'practice';
    }

    /**
     * Seconds left on the server-authoritative timer.
     */
    public function remainingSeconds(): int
    {
        if ($this->ends_at === null) {
            return 0;
        }

        if ($this->status !== 'in_progress') {
            return 0;
        }

        return max(0, now()->diffInSeconds($this->ends_at, false));
    }

    /**
     * Frozen display order of option IDs for one question.
     */
    public function optionIdsFor(int|string $questionId): array
    {
        return $this->option_orders[$questionId] ?? [];
    }

    /**
     * True when the given question id belongs to the frozen question set.
     */
    public function hasQuestion(int|string $questionId): bool
    {
        return in_array((int) $questionId, array_map(intval(...), $this->question_ids ?? []), true);
    }
}
