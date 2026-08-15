<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class SchemaJson implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        if (is_string($decoded)) {
            $nested = json_decode($decoded, true);

            if (is_array($nested)) {
                return $nested;
            }
        }

        return [];
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if (is_array($value)) {
            return json_encode($value);
        }

        $decoded = json_decode((string) $value, true);

        if (is_array($decoded)) {
            return json_encode($decoded);
        }

        return '[]';
    }
}
