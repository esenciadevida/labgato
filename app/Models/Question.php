<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class Question extends Model
{
    public const TYPES = ['text', 'image', 'text_image', 'calculation', 'case'];

    public const DIFFICULTIES = ['easy', 'intermediate', 'advanced'];

    public const STATUSES = ['draft', 'active', 'archived'];

    public const IMAGE_LICENSES = ['Public Domain', 'CC BY 4.0', 'CC BY-SA 4.0', 'CC BY-NC 4.0', 'Original'];

    protected $fillable = [
        'question_category_id',
        'question_type',
        'stem',
        'case_text',
        'image_path',
        'image_alt',
        'image_source',
        'image_license',
        'image_attribution',
        'image_source_url',
        'difficulty',
        'explanation',
        'reference',
        'status',
    ];

    public function usesImage(): bool
    {
        return in_array($this->question_type, ['image', 'text_image'], true);
    }

    /** Whether this question's image requires attribution (CC license). */
    public function requiresAttribution(): bool
    {
        return $this->image_license !== null && str_contains($this->image_license, 'CC');
    }

    /** Rendered attribution line as safe HTML, linking to the source when present. */
    public function attributionHtml(): string
    {
        if (! $this->requiresAttribution() && ! $this->image_source) {
            return '';
        }

        $parts = [];

        if ($this->image_source) {
            $parts[] = e($this->image_source);
        }

        if ($this->image_license) {
            $parts[] = e($this->image_license);
        }

        if ($this->image_attribution) {
            $parts[] = e($this->image_attribution);
        }

        if ($this->image_source_url) {
            $parts[] = '<a href="'.e($this->image_source_url).'" target="_blank" rel="noopener noreferrer" class="underline">Source</a>';
        }

        return implode(' · ', array_filter($parts));
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function usedInExams(): bool
    {
        if (! Schema::hasTable('exam_answers')) {
            return false;
        }

        return ExamAnswer::where('question_id', $this->id)->exists();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(QuestionCategory::class, 'question_category_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('sort_order');
    }

    public function correctOption(): HasOne
    {
        return $this->hasOne(QuestionOption::class)->where('is_correct', true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }
}
