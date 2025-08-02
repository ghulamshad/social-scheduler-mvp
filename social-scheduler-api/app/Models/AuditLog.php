<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'description',
        'severity',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Get the user that owns the audit log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for audit logs by action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for audit logs by model type
     */
    public function scopeByModelType($query, $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope for audit logs by severity
     */
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope for audit logs by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for critical audit logs
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    /**
     * Scope for error audit logs
     */
    public function scopeErrors($query)
    {
        return $query->whereIn('severity', ['error', 'critical']);
    }

    /**
     * Create audit log entry
     */
    public static function log(
        string $action,
        string $description,
        ?int $userId = null,
        ?string $modelType = null,
        ?int $modelId = null,
        array $oldValues = [],
        array $newValues = [],
        string $severity = 'info'
    ): self {
        return static::create([
            'user_id' => $userId,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
            'severity' => $severity,
        ]);
    }

    /**
     * Log user login
     */
    public static function logLogin(int $userId, string $ipAddress = null): self
    {
        return static::log(
            'login',
            'User logged in successfully',
            $userId,
            null,
            null,
            [],
            [],
            'info'
        );
    }

    /**
     * Log user logout
     */
    public static function logLogout(int $userId): self
    {
        return static::log(
            'logout',
            'User logged out',
            $userId,
            null,
            null,
            [],
            [],
            'info'
        );
    }

    /**
     * Log model creation
     */
    public static function logCreate(
        string $modelType,
        int $modelId,
        array $newValues,
        ?int $userId = null
    ): self {
        return static::log(
            'create',
            "Created new {$modelType}",
            $userId,
            $modelType,
            $modelId,
            [],
            $newValues,
            'info'
        );
    }

    /**
     * Log model update
     */
    public static function logUpdate(
        string $modelType,
        int $modelId,
        array $oldValues,
        array $newValues,
        ?int $userId = null
    ): self {
        return static::log(
            'update',
            "Updated {$modelType}",
            $userId,
            $modelType,
            $modelId,
            $oldValues,
            $newValues,
            'info'
        );
    }

    /**
     * Log model deletion
     */
    public static function logDelete(
        string $modelType,
        int $modelId,
        array $oldValues,
        ?int $userId = null
    ): self {
        return static::log(
            'delete',
            "Deleted {$modelType}",
            $userId,
            $modelType,
            $modelId,
            $oldValues,
            [],
            'warning'
        );
    }

    /**
     * Log security event
     */
    public static function logSecurityEvent(
        string $action,
        string $description,
        ?int $userId = null,
        string $severity = 'warning'
    ): self {
        return static::log(
            $action,
            $description,
            $userId,
            null,
            null,
            [],
            [],
            $severity
        );
    }

    /**
     * Get changed fields between old and new values
     */
    public function getChangedFields(): array
    {
        if (empty($this->old_values) || empty($this->new_values)) {
            return [];
        }

        $changed = [];
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changed[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changed;
    }

    /**
     * Check if this is a security-related log
     */
    public function isSecurityEvent(): bool
    {
        return in_array($this->action, ['login', 'logout', 'failed_login', 'password_reset', 'api_access']);
    }

    /**
     * Check if this is a critical log
     */
    public function isCritical(): bool
    {
        return $this->severity === 'critical';
    }
} 