<?php

namespace SGCart\Tax\Models;

use Illuminate\Database\Eloquent\Model;

class TaxSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get setting value by key, with default fallback.
     */
    public static function getVal(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value by key.
     */
    public static function setVal(string $key, $value): self
    {
        return self::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
