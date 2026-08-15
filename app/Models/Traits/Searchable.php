<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    abstract public static function fulltextSearchColumns(): array;

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $columns = static::fulltextSearchColumns();

        if (in_array($query->getModel()->getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            $booleanTerm = self::booleanQueryTerm($term);

            if ($booleanTerm === '') {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereRaw(
                'MATCH ('.implode(', ', $columns).') AGAINST (? IN BOOLEAN MODE)',
                [$booleanTerm]
            );
        }

        return $query->where(function (Builder $q) use ($term, $columns) {
            foreach ($columns as $index => $column) {
                if ($index === 0) {
                    $q->where($column, 'like', "%{$term}%");
                } else {
                    $q->orWhere($column, 'like', "%{$term}%");
                }
            }
        });
    }

    protected static function booleanQueryTerm(string $term): string
    {
        $term = preg_replace('/[+\-<>()~*"@\/\\\\]/', ' ', $term);
        $term = trim((string) preg_replace('/\s+/', ' ', $term));

        if ($term === '') {
            return '';
        }

        return implode(' ', array_map(fn (string $word) => $word.'*', explode(' ', $term)));
    }
}
