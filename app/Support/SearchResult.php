<?php

namespace App\Support;

use DateTimeInterface;

class SearchResult
{
    public function __construct(
        public readonly string $type,
        public readonly string $title,
        public readonly string $url,
        public readonly ?string $description = null,
        public readonly ?string $categoryName = null,
        public readonly array $tags = [],
        public readonly ?DateTimeInterface $publishedAt = null,
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'url' => $this->url,
            'description' => $this->description,
            'categoryName' => $this->categoryName,
            'tags' => $this->tags,
            'publishedAt' => $this->publishedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
