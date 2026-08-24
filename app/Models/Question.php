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

    protected $fillable = [
        'question_category_id',
        'question_type',
        'stem',
        'case_text',
        'image_path',
        'image_alt',
        'difficulty',
        'explanation',
        'reference',
        'status',
    ];

    public function usesImage(): bool
    {
        return in_array($this->question_type, ['image', 'text_image'], true);
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
