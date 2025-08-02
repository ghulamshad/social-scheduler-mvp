<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'key',
        'value',
        'type',
        'category',
        'description',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    /**
     * Get the user that owns the setting.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get setting value with type casting
     */
    public function getTypedValue()
    {
        $value = $this->value;

        switch ($this->type) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            default:
                return $value;
        }
    }

    /**
     * Set setting value with type casting
     */
    public function setTypedValue($value): void
    {
        switch ($this->type) {
            case 'boolean':
                $this->value = (bool) $value;
                break;
            case 'integer':
                $this->value = (int) $value;
                break;
            case 'json':
                $this->value = is_array($value) ? $value : json_decode($value, true);
                break;
            default:
                $this->value = $value;
        }
    }

    /**
     * Scope for settings by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for settings by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get settings as key-value array
     */
    public static function getSettingsArray($userId, $category = null)
    {
        $query = static::where('user_id', $userId);
        
        if ($category) {
            $query->byCategory($category);
        }
        
        return $query->get()->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->getTypedValue()];
        })->toArray();
    }

    /**
     * Set multiple settings at once
     */
    public static function setMultipleSettings($userId, array $settings, $category = 'general')
    {
        foreach ($settings as $key => $value) {
            $type = is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : 'string');
            
            static::updateOrCreate(
                ['user_id' => $userId, 'key' => $key],
                [
                    'value' => $value,
                    'type' => $type,
                    'category' => $category,
                ]
            );
        }
    }
} 