<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentBlueprint extends Model
{
    protected $fillable = [
        'assessment_id',
        'question_category_id',
        'question_count',
        'easy_percent',
        'intermediate_percent',
        'advanced_percent',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(QuestionCategory::class, 'question_category_id');
    }
}
