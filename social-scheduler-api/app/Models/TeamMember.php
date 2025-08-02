<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'user_id',
        'role',
        'permissions',
        'joined_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'joined_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'owner') {
            return true;
        }

        return in_array($permission, $this->permissions ?? []);
    }

    public function canEditPosts(): bool
    {
        return in_array($this->role, ['owner', 'admin', 'editor']);
    }

    public function canApprovePosts(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }

    public function canManageTeam(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }
} 