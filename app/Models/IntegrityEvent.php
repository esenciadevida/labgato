<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrityEvent extends Model
{
    public const TYPES = [
        'tab_switch',
        'window_blur',
        'fullscreen_exit',
        'copy_attempt',
        'paste_attempt',
        'cut_attempt',
        'print_attempt',
        'right_click_attempt',
        'session_change',
        'multiple_session',
        'unusual_timing',
    ];

    public const WEIGHTS = [
        'tab_switch' => 1,
        'window_blur' => 1,
        'fullscreen_exit' => 1,
        'right_click_attempt' => 1,
        'copy_attempt' => 2,
        'paste_attempt' => 2,
        'cut_attempt' => 2,
        'print_attempt' => 2,
        'session_change' => 5,
        'multiple_session' => 5,
        'unusual_timing' => 3,
    ];

    protected $fillable = [
        'exam_attempt_id',
        'user_id',
        'event_type',
        'question_number',
        'weight',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'weight' => 'integer',
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

    public static function weightFor(string $eventType): int
    {
        return self::WEIGHTS[$eventType] ?? 1;
    }
}
