<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    public const STATUSES = ['draft', 'published', 'archived'];

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'description',
        'status',
        'sort_order',
        'certificate_enabled',
        'certificate_requires_all',
        'cert_body_text',
        'cert_disclaimer_text',
        'cert_primary_color',
        'cert_secondary_color',
        'cert_signatory_name',
        'cert_signatory_title',
        'cert_show_logo',
        'cert_show_qr',
    ];

    protected function casts(): array
    {
        return [
            'certificate_enabled' => 'boolean',
            'certificate_requires_all' => 'boolean',
            'cert_show_logo' => 'boolean',
            'cert_show_qr' => 'boolean',
        ];
    }

    public function assessments(): BelongsToMany
    {
        return $this->belongsToMany(Assessment::class, 'course_assessment')
            ->withPivot('required', 'sort_order')
            ->orderByPivot('sort_order');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Assessments that must be passed for course completion.
     */
    public function requiredAssessments(): BelongsToMany
    {
        return $this->assessments()->wherePivot('required', true);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}