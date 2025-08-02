<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGeneration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'type',
        'prompt',
        'generated_content',
        'model',
        'tokens_used',
        'cost',
        'metadata',
    ];

    protected $casts = [
        'generated_content' => 'array',
        'metadata' => 'array',
        'cost' => 'decimal:4',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function getTotalCostAttribute(): float
    {
        return $this->cost ?? 0.0;
    }

    public function getGeneratedTextAttribute(): string
    {
        return $this->generated_content['text'] ?? '';
    }

    public function getGeneratedHashtagsAttribute(): array
    {
        return $this->generated_content['hashtags'] ?? [];
    }
} 