<?php

namespace App\Models;

use App\Casts\SchemaJson;
use App\Enums\ResourceStatus;
use App\Models\Traits\HasResourceLifecycle;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calculator extends Model
{
    use HasFactory;
    use HasResourceLifecycle;
    use Searchable;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'formula_display',
        'input_schema',
        'output_schema',
        'engine',
        'engine_reference',
        'interpretation_notes',
        'references',
        'status',
        'published_at',
        'created_by',
        'updated_by',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'input_schema' => SchemaJson::class,
            'output_schema' => SchemaJson::class,
            'status' => ResourceStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function fulltextSearchColumns(): array
    {
        return ['name', 'short_description'];
    }
}
