<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasSeoEeat
{
    public function effectiveSeoTitle(): string
    {
        $value = $this->getAttributeFromArray('seo_title');

        if ($value !== null && trim((string) $value) !== '') {
            return (string) $value;
        }

        $name = (string) $this->name;
        $keyword = $this->focus_keyword;

        if ($keyword !== null && trim($keyword) !== '' && ! Str::contains(Str::lower($name), Str::lower($keyword))) {
            return "{$name} — {$keyword}";
        }

        return $name;
    }

    public function effectiveSeoDescription(): string
    {
        $value = $this->getAttributeFromArray('seo_description');

        if ($value !== null && trim((string) $value) !== '') {
            return (string) $value;
        }

        $source = $this->short_description ?: ($this->content ?? '');

        return Str::limit(trim(strip_tags((string) $source)), 160);
    }

    public function getDisplayAuthorNameAttribute(): ?string
    {
        if ($this->author_name !== null && trim((string) $this->author_name) !== '') {
            return $this->author_name;
        }

        return $this->contributor?->name;
    }

    public function hasReviewInfo(): bool
    {
        return ($this->reviewer_name !== null && trim((string) $this->reviewer_name) !== '')
            || $this->last_reviewed_at !== null;
    }
}
