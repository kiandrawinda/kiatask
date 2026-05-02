<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecretLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'title',
        'message',
        'unlock_condition',
        'unlock_ref_id',
        'unlock_streak_count',
        'unlock_date',
        'is_unlocked',
        'unlocked_at',
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
}