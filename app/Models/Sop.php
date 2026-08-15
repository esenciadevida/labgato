<?php

namespace App\Models;

use App\Enums\ResourceStatus;
use App\Models\Traits\HasResourceLifecycle;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sop extends Model
{
    use HasFactory;
    use HasResourceLifecycle;
    use Searchable;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'content',
        'purpose',
        'scope',
        'procedure_content',
        'quality_control',
        'safety_information',
        'references',
        'department',
        'version',
        'author_name',
        'reviewed_date',
        'mandatory_disclaimer',
        'last_verified_at',
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
            'last_verified_at' => 'date',
            'reviewed_date' => 'date',
            'status' => ResourceStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'sop_category');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'sop_tag');
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

    public function versions()
    {
        return $this->hasMany(SopVersion::class, 'sop_id');
    }

    public static function fulltextSearchColumns(): array
    {
        return ['name', 'short_description', 'content'];
    }
}
