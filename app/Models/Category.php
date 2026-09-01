<?php

namespace App\Models;

use App\View\Composers\TaxonomyComposer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'parent_id', 'sort_order', 'is_active'];

    protected static function booted(): void
    {
        static::saved(fn () => TaxonomyComposer::flushCache());
        static::deleted(fn () => TaxonomyComposer::flushCache());
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
