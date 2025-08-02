<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'logo_url',
        'subdomain',
        'branding',
        'is_active',
    ];

    protected $casts = [
        'branding' => 'array',
        'is_active' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function hasMember(int $userId): bool
    {
        return $this->members()->where('user_id', $userId)->exists();
    }

    public function getMemberRole(int $userId): ?string
    {
        $member = $this->members()->where('user_id', $userId)->first();
        return $member ? $member->role : null;
    }

    public function canUserEdit(int $userId): bool
    {
        $role = $this->getMemberRole($userId);
        return in_array($role, ['owner', 'admin', 'editor']);
    }

    public function canUserApprove(int $userId): bool
    {
        $role = $this->getMemberRole($userId);
        return in_array($role, ['owner', 'admin']);
    }
} 