<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'category',
        'data',
        'read_at',
        'sent_at',
        'channel',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for notifications by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for notifications by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for notifications by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for pending notifications
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Mark notification as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark notification as failed
     */
    public function markAsFailed(string $errorMessage = null): void
    {
        $this->update([
            'status' => 'failed',
            'data' => array_merge($this->data ?? [], ['error' => $errorMessage]),
        ]);
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Check if notification is unread
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * Get notification data value
     */
    public function getDataValue(string $key, $default = null)
    {
        return data_get($this->data, $key, $default);
    }

    /**
     * Create a new notification
     */
    public static function createNotification(
        int $userId,
        string $type,
        string $title,
        string $message,
        string $category = 'general',
        array $data = [],
        string $channel = 'database'
    ): self {
        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'category' => $category,
            'data' => $data,
            'channel' => $channel,
            'status' => 'pending',
        ]);
    }

    /**
     * Create success notification
     */
    public static function createSuccess(
        int $userId,
        string $title,
        string $message,
        string $category = 'general',
        array $data = []
    ): self {
        return static::createNotification($userId, 'success', $title, $message, $category, $data);
    }

    /**
     * Create error notification
     */
    public static function createError(
        int $userId,
        string $title,
        string $message,
        string $category = 'general',
        array $data = []
    ): self {
        return static::createNotification($userId, 'error', $title, $message, $category, $data);
    }

    /**
     * Create warning notification
     */
    public static function createWarning(
        int $userId,
        string $title,
        string $message,
        string $category = 'general',
        array $data = []
    ): self {
        return static::createNotification($userId, 'warning', $title, $message, $category, $data);
    }

    /**
     * Create info notification
     */
    public static function createInfo(
        int $userId,
        string $title,
        string $message,
        string $category = 'general',
        array $data = []
    ): self {
        return static::createNotification($userId, 'info', $title, $message, $category, $data);
    }
} 