<?php

namespace App\Models;

use App\Casts\SchemaJson;
use App\Enums\ResourceStatus;
use App\Models\Traits\HasResourceLifecycle;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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

    protected static function booted(): void
    {
        // Keep the reference-range "related calculators" cache in sync when a
        // calculator's referenced parameters change.
        static::saved(fn (Calculator $calculator) => $calculator->flushRelatedReferenceRangeCache());
        static::deleted(fn (Calculator $calculator) => $calculator->flushRelatedReferenceRangeCache());
    }

    public function flushRelatedReferenceRangeCache(): void
    {
        foreach (collect($this->output_schema ?? [])->pluck('reference_key')->filter() as $key) {
            Cache::forget("reference-range-calculators:{$key}");
        }
    }

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
