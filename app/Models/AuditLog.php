<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'resource_type',
        'resource_id',
        'changes',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function formatAction(string $action): string
    {
        return collect(explode('.', $action))
            ->map(fn ($segment) => str($segment)->replace('_', ' ')->ucfirst())
            ->implode(' · ');
    }

    public function getActionLabelAttribute(): string
    {
        return static::formatAction($this->action);
    }
}
