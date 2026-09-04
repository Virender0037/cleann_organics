<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    private const CACHE_PREFIX = 'settings.';

    public static function group(string $group): array
    {
        return static::where('group', $group)->pluck('value', 'key')->all();
    }

    /**
     * Same as group(), but cached indefinitely — for values read on every
     * request (e.g. the storefront layout) rather than only on an admin
     * settings screen. Callers that mutate a group must call forget() for
     * that group afterwards.
     */
    public static function cached(string $group): array
    {
        return Cache::rememberForever(self::CACHE_PREFIX.$group, fn () => static::group($group));
    }

    public static function forget(string $group): void
    {
        Cache::forget(self::CACHE_PREFIX.$group);
    }

    public static function setMany(string $group, array $data): void
    {
        foreach ($data as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        }
    }
}
