<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'partner_id', 'partner_code', 'avatar', 'timezone',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            if (empty($user->partner_code)) {
                $user->partner_code = strtoupper(Str::random(8));
            }
        });
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function goals()
    {
        return $this->hasMany(Goal::class, 'owner_id');
    }

    public function moods()
    {
        return $this->hasMany(Mood::class);
    }

    public function streak()
    {
        return $this->hasOne(Streak::class);
    }

    public function focusSessions()
    {
        return $this->hasMany(FocusSession::class);
    }

    public function sentLetters()
    {
        return $this->hasMany(SecretLetter::class, 'sender_id');
    }

    public function receivedLetters()
    {
        return $this->hasMany(SecretLetter::class, 'receiver_id');
    }

    public function notifications()
    {
        return $this->hasMany(AppNotification::class);
    }

    public function specialDates()
    {
        return $this->hasMany(SpecialDate::class);
    }

    public function hasPartner(): bool
    {
        return !is_null($this->partner_id);
    }

    public function todayMood()
    {
        return $this->moods()->whereDate('date', today())->first();
    }

    public function currentStreak()
    {
        return $this->streak?->current_streak ?? 0;
    }

    public function getActiveFocusSession()
    {
        return $this->focusSessions()->where('status', 'active')->latest()->first();
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        return strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
    }
}