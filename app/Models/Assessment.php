<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Assessment extends Model
{
    public const TYPES = ['challenge', 'mock', 'practice', 'daily'];

    public const STATUSES = ['draft', 'scheduled', 'open', 'closed', 'archived'];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'type',
        'status',
        'duration_minutes',
        'total_questions',
        'opens_at',
        'closes_at',
        'max_attempts',
        'allow_practice',
        'shuffle_questions',
        'shuffle_options',
        'fullscreen_required',
        'pass_percentage',
        'certificate_enabled',
        'results_status',
        'answer_review_status',
        'leaderboard_status',
    ];

    protected function casts(): array
    {
        return [
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'allow_practice' => 'boolean',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'fullscreen_required' => 'boolean',
            'pass_percentage' => 'decimal:2',
            'certificate_enabled' => 'boolean',
        ];
    }

    public function blueprints(): HasMany
    {
        return $this->hasMany(AssessmentBlueprint::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function isOpen(): bool
    {
        if ($this->status !== 'open') {
            return false;
        }

        $now = now();

        return ($this->opens_at === null || $now->gte($this->opens_at))
            && ($this->closes_at === null || $now->lte($this->closes_at));
    }

    public function isLockedResults(): bool
    {
        return $this->results_status === 'locked';
    }

    public function isLockedAnswerReview(): bool
    {
        return $this->answer_review_status === 'locked';
    }

    public function isLockedLeaderboard(): bool
    {
        return $this->leaderboard_status === 'locked';
    }

    /**
     * Total questions demanded by the blueprint; useful for validating that
     * the configured total_questions matches the per-category plan.
     */
    public function blueprintQuestionCount(): int
    {
        return (int) $this->blueprints()->sum('question_count');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open')
            ->where(function ($q) {
                $q->whereNull('opens_at')->orWhere('opens_at', '<=', DB::raw('NOW()'));
            })
            ->where(function ($q) {
                $q->whereNull('closes_at')->orWhere('closes_at', '>=', DB::raw('NOW()'));
            });
    }

    public function scopeChallenges($query)
    {
        return $query->where('type', 'challenge');
    }
}
