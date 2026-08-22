<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentImage extends Model
{
    protected $fillable = [
        'uploaded_by',
        'resource_type',
        'resource_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
        'width',
        'height',
        'attached_at',
    ];

    protected function casts(): array
    {
        return [
            'attached_at' => 'datetime',
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function attachTo(string $resourceType, int $resourceId): void
    {
        if ($this->attached_at !== null) {
            return;
        }

        $this->forceFill([
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'attached_at' => now(),
        ])->save();
    }
}
