<?php

namespace App\Models;

use App\Enums\ResourceStatus;
use App\Models\Concerns\HasSeoEeat;
use App\Models\Traits\HasResourceLifecycle;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterpretationResource extends Model
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
        'clinical_context',
        'related_analytes',
        'mandatory_disclaimer',
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
            'related_analytes' => 'array',
            'status' => ResourceStatus::class,
            'published_at' => 'datetime',
            'last_reviewed_at' => 'date',
            'faq' => 'array',
        ];
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'interpretation_resource_category');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'interpretation_resource_tag');
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
        return ['name', 'short_description', 'clinical_context'];
    }
}
