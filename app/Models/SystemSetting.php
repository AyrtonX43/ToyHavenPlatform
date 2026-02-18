<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    /**
     * Get setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return match($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    /**
     * Set setting value by key
     */
    public static function set($key, $value, $type = 'string', $group = 'general', $description = null)
    {
        $setting = self::firstOrNew(['key' => $key]);
        
        $setting->value = is_array($value) ? json_encode($value) : $value;
        $setting->type = $type;
        $setting->group = $group;
        $setting->description = $description;
        
        return $setting->save();
    }
}
