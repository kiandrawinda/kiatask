<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'assigned_to', 'title', 'description', 'type',
        'deadline', 'priority', 'status', 'progress', 'completed_at',
    ];

    protected $casts = [
        'deadline' => 'date',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function isOverdue(): bool
    {
        return $this->deadline && $this->deadline->isPast() && $this->status !== 'done';
    }

    public function isDueSoon(): bool
    {
        return $this->deadline && $this->deadline->diffInDays(now()) <= 2 && $this->status !== 'done';
    }

    public function getDaysUntilDeadlineAttribute(): ?int
    {
        if (!$this->deadline) return null;
        return now()->diffInDays($this->deadline, false);
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'high' => 'rose',
            'medium' => 'amber',
            'low' => 'emerald',
            default => 'slate',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'done' => 'emerald',
            'on_progress' => 'violet',
            'pending' => 'slate',
            default => 'slate',
        };
    }

    public function scopePersonal($query)
    {
        return $query->where('type', 'personal');
    }

    public function scopeShared($query)
    {
        return $query->where('type', 'shared');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)->orWhere('assigned_to', $userId);
        });
    }
}