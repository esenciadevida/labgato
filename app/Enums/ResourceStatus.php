<?php

namespace App\Enums;

enum ResourceStatus: string
{
    case DRAFT = 'draft';
    case UNDER_REVIEW = 'under_review';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
    case OBSOLETE = 'obsolete';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::UNDER_REVIEW => 'Under Review',
            self::PUBLISHED => 'Published',
            self::ARCHIVED => 'Archived',
            self::OBSOLETE => 'Obsolete',
            self::REJECTED => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::UNDER_REVIEW => 'yellow',
            self::PUBLISHED => 'green',
            self::ARCHIVED => 'blue',
            self::OBSOLETE => 'red',
            self::REJECTED => 'red',
        };
    }

    public function isPublic(): bool
    {
        return $this === self::PUBLISHED;
    }

    public function canTransitionTo(ResourceStatus $target): bool
    {
        return match ($this) {
            self::DRAFT => in_array($target, [self::UNDER_REVIEW, self::PUBLISHED, self::ARCHIVED, self::OBSOLETE]),
            self::UNDER_REVIEW => in_array($target, [self::DRAFT, self::PUBLISHED, self::REJECTED, self::ARCHIVED, self::OBSOLETE]),
            self::PUBLISHED => in_array($target, [self::ARCHIVED, self::OBSOLETE]),
            self::ARCHIVED => in_array($target, [self::DRAFT, self::PUBLISHED, self::OBSOLETE]),
            self::OBSOLETE => in_array($target, [self::ARCHIVED]),
            self::REJECTED => in_array($target, [self::DRAFT, self::UNDER_REVIEW]),
        };
    }

    public function canMemberEdit(): bool
    {
        return in_array($this, [self::DRAFT, self::REJECTED], true);
    }

    public function canMemberWithdraw(): bool
    {
        return $this === self::UNDER_REVIEW;
    }

    public function canMemberDelete(): bool
    {
        return in_array($this, [self::DRAFT, self::REJECTED], true);
    }
}
