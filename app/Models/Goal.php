<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id', 'partner_id', 'title', 'description', 'category',
        'target_value', 'current_value', 'unit', 'deadline', 'is_completed', 'completed_at',
    ];

    protected $casts = [
        'deadline' => 'date',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function getProgressPercentageAttribute(): int
    {
        if ($this->target_value == 0) return 0;
        return min(100, (int)(($this->current_value / $this->target_value) * 100));
    }

    public function getDaysUntilDeadlineAttribute(): ?int
    {
        if (!$this->deadline) return null;
        return now()->diffInDays($this->deadline, false);
    }
}