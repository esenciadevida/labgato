<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsage extends Model
{
    use HasFactory;

    protected $table = 'ai_usage';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'provider',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'latency_ms',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'latency_ms' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
