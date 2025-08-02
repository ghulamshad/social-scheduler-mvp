<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_id',
        'content',
        'hashtags',
        'tone',
        'ai_generation_data',
        'image_path',
        'schedule_time',
        'status',
        'approval_status',
        'approved_by',
        'approved_at',
        'platform_constraints',
        'passes_validation',
        'validation_errors',
        'character_count',
        'performance_metrics',
        'template_id',
        'is_recycled',
        'recycled_at',
        'team_id',
        'published_at',
        'publish_log',
    ];

    protected $casts = [
        'hashtags' => 'array',
        'ai_generation_data' => 'array',
        'platform_constraints' => 'array',
        'validation_errors' => 'array',
        'performance_metrics' => 'array',
        'schedule_time' => 'datetime',
        'approved_at' => 'datetime',
        'recycled_at' => 'datetime',
        'published_at' => 'datetime',
        'passes_validation' => 'boolean',
        'is_recycled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ContentTemplate::class, 'template_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PostApproval::class);
    }

    public function aiGenerations(): HasMany
    {
        return $this->hasMany(AiGeneration::class);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeDueForPublishing($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('schedule_time', '<=', now());
    }

    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', 'pending_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeByTeam($query, int $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeRecycled($query)
    {
        return $query->where('is_recycled', true);
    }

    public function markAsPublished(string $log = null): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
            'publish_log' => $log ?? 'Successfully published',
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'publish_log' => $error,
        ]);
    }

    public function requestApproval(int $requestedBy): void
    {
        $this->update(['approval_status' => 'pending_approval']);
        
        PostApproval::create([
            'post_id' => $this->id,
            'requested_by' => $requestedBy,
            'status' => 'pending',
        ]);
    }

    public function approve(int $approvedBy, string $comments = null): void
    {
        $this->update([
            'approval_status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        $this->approvals()->where('status', 'pending')->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'comments' => $comments,
        ]);
    }

    public function reject(int $rejectedBy, string $comments = null): void
    {
        $this->update(['approval_status' => 'rejected']);

        $this->approvals()->where('status', 'pending')->update([
            'status' => 'rejected',
            'approved_by' => $rejectedBy,
            'approved_at' => now(),
            'comments' => $comments,
        ]);
    }

    public function validateContent(): array
    {
        $errors = [];
        $platform = $this->account->platform ?? 'unknown';
        
        // Character count validation
        $this->character_count = strlen($this->content);
        $this->save();

        switch ($platform) {
            case 'twitter':
                if ($this->character_count > 280) {
                    $errors[] = 'Content exceeds Twitter character limit (280)';
                }
                break;
            case 'instagram':
                if (empty($this->image_path)) {
                    $errors[] = 'Instagram requires an image';
                }
                break;
            case 'linkedin':
                if ($this->character_count > 3000) {
                    $errors[] = 'Content exceeds LinkedIn character limit (3000)';
                }
                break;
        }

        $this->update([
            'passes_validation' => empty($errors),
            'validation_errors' => $errors,
        ]);

        return $errors;
    }

    public function getEngagementRateAttribute(): float
    {
        $metrics = $this->performance_metrics ?? [];
        $likes = $metrics['likes'] ?? 0;
        $shares = $metrics['shares'] ?? 0;
        $comments = $metrics['comments'] ?? 0;
        $impressions = $metrics['impressions'] ?? 1;

        return $impressions > 0 ? (($likes + $shares + $comments) / $impressions) * 100 : 0;
    }

    public function getTotalEngagementAttribute(): int
    {
        $metrics = $this->performance_metrics ?? [];
        return ($metrics['likes'] ?? 0) + ($metrics['shares'] ?? 0) + ($metrics['comments'] ?? 0);
    }
}
