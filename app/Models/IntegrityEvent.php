<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrityEvent extends Model
{
    public const EVENT_TYPES = [
        'tab_switch',
        'window_blur',
        'fullscreen_exit',
        'copy_attempt',
        'paste_attempt',
        'print_attempt',
        'contextmenu',
        'session_conflict',
        'other',
    ];

    protected $fillable = [
        'exam_attempt_id',
        'user_id',
        'event_type',
        'question_number',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
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
}
