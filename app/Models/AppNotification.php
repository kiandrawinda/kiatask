<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'icon',
        'read_status',
        'action_url'
    ];

    protected $casts = [
        'read_status' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}