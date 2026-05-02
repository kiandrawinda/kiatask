<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Streak extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'current_streak', 'longest_streak', 'last_completed_date'];

    protected $casts = ['last_completed_date' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getBadgeAttribute(): ?string
    {
        $streak = $this->current_streak;
        if ($streak >= 100) return '💎 100 Days Legend';
        if ($streak >= 30) return '🔥 30 Days Discipline Mode';
        if ($streak >= 14) return '⚡ 14 Days Power Couple';
        if ($streak >= 7) return '✨ 7 Days Power Couple';
        if ($streak >= 3) return '🌱 3 Days Growing Strong';
        return null;
    }
}

class FocusSession extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'started_at', 'ended_at', 'duration_minutes', 'status', 'note'];

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
        return $this->started_at->addMinutes($this->duration_minutes)->isPast();
    }
}

class SecretLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id', 'receiver_id', 'title', 'message', 'unlock_condition',
        'unlock_ref_id', 'unlock_streak_count', 'unlock_date', 'is_unlocked', 'unlocked_at',
    ];

    protected $casts = [
        'is_unlocked' => 'boolean',
        'unlocked_at' => 'datetime',
        'unlock_date' => 'date',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function getUnlockDescriptionAttribute(): string
    {
        return match($this->unlock_condition) {
            'task_complete' => 'Complete a specific task',
            'goal_reached' => 'Reach a couple goal',
            'streak' => "Maintain {$this->unlock_streak_count} day streak",
            'date' => 'On ' . optional($this->unlock_date)->format('M d, Y'),
            default => 'Unknown condition',
        };
    }
}

class AppNotification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = ['user_id', 'title', 'message', 'type', 'icon', 'read_status', 'action_url'];

    protected $casts = ['read_status' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class SpecialDate extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'description', 'date', 'is_recurring', 'emoji'];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDaysUntilAttribute(): int
    {
        $date = $this->date;
        if ($this->is_recurring) {
            $date = $date->setYear(now()->year);
            if ($date->isPast()) {
                $date = $date->addYear();
            }
        }
        return now()->diffInDays($date, false);
    }

    public function getNextOccurrenceAttribute(): \Carbon\Carbon
    {
        $date = $this->date->copy();
        if ($this->is_recurring) {
            $date->year = now()->year;
            if ($date->isPast()) {
                $date->addYear();
            }
        }
        return $date;
    }
}