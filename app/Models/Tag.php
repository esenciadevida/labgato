<?php

namespace App\Models;

use App\View\Composers\TaxonomyComposer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    protected static function booted(): void
    {
        static::saved(fn () => TaxonomyComposer::flushCache());
        static::deleted(fn () => TaxonomyComposer::flushCache());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function normalizeName(string $name): string
    {
        return Str::lower(trim($name));
    }
}
