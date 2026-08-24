<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $fillable = [
        'user_id',
        'exam_attempt_id',
        'certificate_number',
        'verification_uuid',
        'title',
        'score',
        'percentage',
        'issued_at',
        'revoked',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
            'issued_at' => 'datetime',
            'revoked' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query->where('revoked', false);
    }
}
