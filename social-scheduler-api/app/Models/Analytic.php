<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Analytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'platform',
        'platform_post_id',
        'likes',
        'shares',
        'comments',
        'clicks',
        'impressions',
        'reach',
        'engagement_rate',
        'additional_metrics',
        'last_updated',
    ];

    protected $casts = [
        'additional_metrics' => 'array',
        'last_updated' => 'datetime',
        'engagement_rate' => 'decimal:2',
    ];

    /**
     * Get the post that owns the analytic.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user that owns the analytic.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for analytics by platform
     */
    public function scopeByPlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope for analytics by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for analytics by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Calculate total engagement
     */
    public function getTotalEngagementAttribute(): int
    {
        return $this->likes + $this->shares + $this->comments;
    }

    /**
     * Calculate engagement rate
     */
    public function calculateEngagementRate(): float
    {
        if ($this->reach > 0) {
            $this->engagement_rate = ($this->getTotalEngagementAttribute() / $this->reach) * 100;
            $this->save();
        }
        
        return $this->engagement_rate;
    }

    /**
     * Update metrics from platform data
     */
    public function updateMetrics(array $metrics): void
    {
        $this->update([
            'likes' => $metrics['likes'] ?? $this->likes,
            'shares' => $metrics['shares'] ?? $this->shares,
            'comments' => $metrics['comments'] ?? $this->comments,
            'clicks' => $metrics['clicks'] ?? $this->clicks,
            'impressions' => $metrics['impressions'] ?? $this->impressions,
            'reach' => $metrics['reach'] ?? $this->reach,
            'additional_metrics' => array_merge($this->additional_metrics ?? [], $metrics),
            'last_updated' => now(),
        ]);

        $this->calculateEngagementRate();
    }

    /**
     * Get platform-specific metric
     */
    public function getPlatformMetric(string $key, $default = null)
    {
        return data_get($this->additional_metrics, $key, $default);
    }

    /**
     * Get performance summary
     */
    public function getPerformanceSummary(): array
    {
        return [
            'total_engagement' => $this->total_engagement,
            'engagement_rate' => $this->engagement_rate,
            'reach' => $this->reach,
            'impressions' => $this->impressions,
            'clicks' => $this->clicks,
            'last_updated' => $this->last_updated?->diffForHumans(),
        ];
    }

    /**
     * Check if analytics are stale (older than 24 hours)
     */
    public function isStale(): bool
    {
        return $this->last_updated && $this->last_updated->diffInHours(now()) > 24;
    }
} 