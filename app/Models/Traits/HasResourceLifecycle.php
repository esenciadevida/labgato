<?php

namespace App\Models\Traits;

use App\Enums\ResourceStatus;
use Illuminate\Database\Eloquent\Builder;

trait HasResourceLifecycle
{
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ResourceStatus::PUBLISHED);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', ResourceStatus::DRAFT);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', ResourceStatus::ARCHIVED);
    }

    public function scopeObsolete(Builder $query): Builder
    {
        return $query->where('status', ResourceStatus::OBSOLETE);
    }

    public function isPublished(): bool
    {
        return $this->status === ResourceStatus::PUBLISHED;
    }

    public function isDraft(): bool
    {
        return $this->status === ResourceStatus::DRAFT;
    }

    public function isArchived(): bool
    {
        return $this->status === ResourceStatus::ARCHIVED;
    }

    public function publish(): bool
    {
        return $this->update([
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => $this->published_at ?? now(),
        ]);
    }

    public function archive(): bool
    {
        return $this->update(['status' => ResourceStatus::ARCHIVED]);
    }

    public function markObsolete(): bool
    {
        return $this->update(['status' => ResourceStatus::OBSOLETE]);
    }
}
