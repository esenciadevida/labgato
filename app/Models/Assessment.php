<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'cert_body_text',
        'cert_disclaimer_text',
        'cert_primary_color',
        'cert_secondary_color',
        'cert_signatory_name',
        'cert_signatory_title',
        'cert_show_logo',
        'cert_show_qr',
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
            'cert_show_logo' => 'boolean',
            'cert_show_qr' => 'boolean',
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

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_assessment')
            ->withPivot('required', 'sort_order')
            ->orderByPivot('sort_order');
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
        $now = now();

        return $query->where('status', 'open')
            ->where(function ($q) use ($now) {
                $q->whereNull('opens_at')->orWhere('opens_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('closes_at')->orWhere('closes_at', '>=', $now);
            });
    }

    public function scopeChallenges($query)
    {
        return $query->where('type', 'challenge');
    }
}
