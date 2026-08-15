<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsDailyMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'resource_type',
        'status',
        'total_count',
        'new_count',
        'search_volume',
        'search_zero_results',
        'ai_requests_total',
        'ai_requests_failed',
        'ai_tokens_used',
    ];

    protected function casts(): array
    {
        return [
            'total_count' => 'integer',
            'new_count' => 'integer',
            'search_volume' => 'integer',
            'search_zero_results' => 'integer',
            'ai_requests_total' => 'integer',
            'ai_requests_failed' => 'integer',
            'ai_tokens_used' => 'integer',
        ];
    }
}
