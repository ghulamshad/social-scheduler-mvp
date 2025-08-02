<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Webhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'account_id',
        'platform',
        'event_type',
        'webhook_id',
        'payload',
        'status',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the webhook.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post associated with the webhook.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the account associated with the webhook.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Scope for webhooks by platform
     */
    public function scopeByPlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope for webhooks by event type
     */
    public function scopeByEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope for webhooks by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for unprocessed webhooks
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('status', 'received');
    }

    /**
     * Scope for failed webhooks
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Mark webhook as processed
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark webhook as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'processed_at' => now(),
        ]);
    }

    /**
     * Get webhook payload value by key
     */
    public function getPayloadValue(string $key, $default = null)
    {
        return data_get($this->payload, $key, $default);
    }

    /**
     * Check if webhook is for a specific platform
     */
    public function isForPlatform(string $platform): bool
    {
        return $this->platform === $platform;
    }

    /**
     * Check if webhook is for a specific event type
     */
    public function isEventType(string $eventType): bool
    {
        return $this->event_type === $eventType;
    }
} 