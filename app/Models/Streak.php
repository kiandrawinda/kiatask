<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Streak extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'current_streak',
        'longest_streak',
        'last_completed_date'
    ];

    protected $casts = [
        'last_completed_date' => 'date'
    ];

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