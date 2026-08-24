<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    public const TYPES = ['participation', 'achievement', 'distinction'];

    public const LABELS = [
        'participation' => 'Certificate of Participation',
        'achievement' => 'Certificate of Achievement',
        'distinction' => 'Certificate of Distinction',
    ];

    protected $fillable = [
        'exam_attempt_id',
        'user_id',
        'assessment_id',
        'certificate_number',
        'verification_code',
        'type',
        'score_percent',
        'rank_at_issue',
        'pdf_path',
        'issued_at',
        'revoked_at',
        'revoke_reason',
    ];

    protected function casts(): array
    {
        return [
            'score_percent' => 'float',
            'issued_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query->whereNull('revoked_at');
    }

    public function label(): string
    {
        return self::LABELS[$this->type] ?? ucfirst($this->type);
    }

    public function verifyUrl(): string
    {
        return url("/verify/{$this->verification_code}");
    }

    public function shareUrl(): string
    {
        return url("/share/{$this->verification_code}");
    }
}
