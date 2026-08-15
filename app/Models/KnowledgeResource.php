<?php

namespace App\Models;

use App\Enums\ResourceStatus;
use App\Models\Traits\HasResourceLifecycle;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeResource extends Model
{
    use HasFactory;
    use HasResourceLifecycle;
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
    ];

    protected function casts(): array
    {
        return [
            'status' => ResourceStatus::class,
            'published_at' => 'datetime',
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
}
