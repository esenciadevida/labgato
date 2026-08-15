<?php

namespace App\Enums;

use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\Sop;

enum ContributionType: string
{
    case SOP = 'sop';
    case KNOWLEDGE = 'knowledge';
    case INTERPRETATION = 'interpretation';

    public function label(): string
    {
        return match ($this) {
            self::SOP => 'SOP',
            self::KNOWLEDGE => 'Knowledge',
            self::INTERPRETATION => 'Interpretation',
        };
    }

    public function pluralLabel(): string
    {
        return match ($this) {
            self::SOP => 'SOPs',
            self::KNOWLEDGE => 'Knowledge',
            self::INTERPRETATION => 'Interpretation',
        };
    }

    public function model(): string
    {
        return match ($this) {
            self::SOP => Sop::class,
            self::KNOWLEDGE => KnowledgeResource::class,
            self::INTERPRETATION => InterpretationResource::class,
        };
    }

    public static function resolve(?string $type): ?self
    {
        if ($type === null) {
            return null;
        }

        return self::tryFrom($type);
    }
}
