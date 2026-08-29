<?php

namespace App\Models;

use App\Enums\ResourceStatus;
use App\Models\Traits\HasResourceLifecycle;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferenceRange extends Model
{
    use HasFactory;
    use HasResourceLifecycle;
    use Searchable;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'analyte_name',
        'parameter_key',
        'specimen_type',
        'methodology',
        'population_group',
        'age_min',
        'age_max',
        'sex',
        'unit',
        'si_unit',
        'lower_limit',
        'upper_limit',
        'critical_low',
        'critical_high',
        'conversion_factor',
        'source_citation',
        'last_verified_at',
        'effective_date',
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
            'age_min' => 'float',
            'age_max' => 'float',
            'lower_limit' => 'float',
            'upper_limit' => 'float',
            'critical_low' => 'float',
            'critical_high' => 'float',
            'conversion_factor' => 'float',
            'last_verified_at' => 'date',
            'effective_date' => 'date',
            'status' => ResourceStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'reference_range_category');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'reference_range_tag');
    }

    public function sops()
    {
        return $this->belongsToMany(Sop::class, 'sop_reference_range');
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
        return ['name', 'analyte_name', 'short_description'];
    }

    public function scopeForAnalyte(Builder $query, string $analyteName): Builder
    {
        return $query->where('analyte_name', $analyteName);
    }

    public function scopeForParameterKey(Builder $query, string $parameterKey): Builder
    {
        return $query->where('parameter_key', $parameterKey);
    }

    public function rangeLabel(): string
    {
        $fmt = function ($value): ?string {
            if ($value === null) {
                return null;
            }

            return floor($value) == $value
                ? number_format($value, 0, '.', '')
                : rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
        };

        $lower = $fmt($this->lower_limit);
        $upper = $fmt($this->upper_limit);

        return trim(($lower ?? '').($lower !== null && $upper !== null ? '–' : '').($upper ?? ''));
    }

    public function referenceDisplay(): string
    {
        $label = $this->rangeLabel();

        return trim($label.($this->unit ? ' '.$this->unit : ''));
    }
}
