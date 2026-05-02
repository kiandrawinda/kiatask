<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FocusSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'started_at',
        'ended_at',
        'duration_minutes',
        'status',
        'note'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRemainingSecondsAttribute(): int
    {
        if ($this->status !== 'active') return 0;

        $elapsed = now()->diffInSeconds($this->started_at);
        $total = $this->duration_minutes * 60;

        return max(0, $total - $elapsed);
    }

    public function isExpired(): bool
    {
        return $this->started_at
            ->addMinutes($this->duration_minutes)
            ->isPast();
    }
}