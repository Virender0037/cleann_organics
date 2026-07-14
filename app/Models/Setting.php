<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    public static function group(string $group): array
    {
        return static::where('group', $group)->pluck('value', 'key')->all();
    }

    public static function setMany(string $group, array $data): void
    {
        foreach ($data as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        }
    }
}
