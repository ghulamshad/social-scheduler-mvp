<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'content',
        'hashtags',
        'platform',
        'category',
        'metadata',
        'is_evergreen',
        'recycle_interval_days',
        'last_used_at',
        'is_active',
    ];

    protected $casts = [
        'hashtags' => 'array',
        'metadata' => 'array',
        'is_evergreen' => 'boolean',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'template_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEvergreen($query)
    {
        return $query->where('is_evergreen', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeDueForRecycling($query)
    {
        return $query->where('is_evergreen', true)
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('last_used_at')
                          ->orWhere('last_used_at', '<=', now()->subDays($this->recycle_interval_days));
                    });
    }

    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    public function getToneAttribute(): string
    {
        return $this->metadata['tone'] ?? 'professional';
    }

    public function getTagsAttribute(): array
    {
        return $this->metadata['tags'] ?? [];
    }

    public function isDueForRecycling(): bool
    {
        if (!$this->is_evergreen || !$this->is_active) {
            return false;
        }

        if (!$this->last_used_at) {
            return true;
        }

        return $this->last_used_at->addDays($this->recycle_interval_days)->isPast();
    }
} 