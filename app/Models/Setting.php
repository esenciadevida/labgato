<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'type', 'group', 'label', 'description', 'options'];

    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        static::flushCache();
    }

    public static function flushCache(): void
    {
        Cache::forget('settings.all');
    }
}
