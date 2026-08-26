<?php

namespace App\Models;

use App\Enums\ResourceStatus;
use App\Models\Concerns\HasSeoEeat;
use App\Models\Traits\HasResourceLifecycle;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class KnowledgeResource extends Model
{
    use HasFactory;
    use HasResourceLifecycle;
    use HasSeoEeat;
    use Searchable;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'content',
        'topic_category',
        'difficulty_level',
        'learning_objectives',
        'author_name',
        'featured_image',
        'reading_time_minutes',
        'status',
        'published_at',
        'created_by',
        'updated_by',
        'contributed_by',
        'rejection_reason',
        'seo_title',
        'seo_description',
        'focus_keyword',
        'reviewer_name',
        'reviewer_credentials',
        'last_reviewed_at',
        'faq',
    ];

    protected function casts(): array
    {
        return [
            'status' => ResourceStatus::class,
            'published_at' => 'datetime',
            'last_reviewed_at' => 'date',
            'faq' => 'array',
        ];
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'knowledge_resource_category');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'knowledge_resource_tag');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function contributedBy()
    {
        return $this->belongsTo(User::class, 'contributed_by');
    }

    public function contributor()
    {
        return $this->belongsTo(User::class, 'contributed_by');
    }

    public static function fulltextSearchColumns(): array
    {
        return ['name', 'short_description', 'content'];
    }

    public function scopeByDifficulty(Builder $query, string $difficulty): Builder
    {
        return $query->where('difficulty_level', $difficulty);
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        $path = $this->featured_image;

        if (! $path) {
            return null;
        }

        if (str_starts_with($path, '/') || preg_match('#^https?:#i', $path)) {
            return $path;
        }

        return Storage::url($path);
    }

    /**
     * @return array{0: int, 1: int}|null
     */
    public function getFeaturedImageDimensionsAttribute(): ?array
    {
        $path = $this->featured_image;

        if (! $path || str_starts_with($path, '/') || preg_match('#^https?:#i', $path)) {
            return null;
        }

        $full = Storage::disk('public')->path($path);

        if (! is_file($full)) {
            return null;
        }

        $dims = @getimagesize($full);

        return $dims === false || $dims[0] < 1 || $dims[1] < 1 ? null : [$dims[0], $dims[1]];
    }
}
