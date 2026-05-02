<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mood extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'mood_level', 'mood_emoji', 'note', 'date'];

    protected $casts = ['date' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getMoodLabelAttribute(): string
    {
        return match($this->mood_level) {
            1 => 'Terrible',
            2 => 'Bad',
            3 => 'Okay',
            4 => 'Good',
            5 => 'Amazing',
            default => 'Unknown',
        };
    }

    public static function getMoodEmoji(int $level): string
    {
        return match($level) {
            1 => '😔',
            2 => '😕',
            3 => '😊',
            4 => '😄',
            5 => '🥰',
            default => '😊',
        };
    }
}