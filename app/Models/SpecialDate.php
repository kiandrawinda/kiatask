<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SpecialDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'date',
        'is_recurring',
        'emoji'
    ];

    protected $casts = [
        'date'         => 'date',
        'is_recurring' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tanggal kemunculan berikutnya.
     * - Kalau recurring: cari tahun ini atau tahun depan
     * - Kalau tidak recurring: tanggal aslinya
     */
    public function getNextOccurrenceAttribute(): ?Carbon
    {
        if (!$this->date) {
            return null;
        }

        if (!$this->is_recurring) {
            return $this->date->copy();
        }

        // Recurring: pakai bulan & hari yang sama, tahun ini
        $today     = Carbon::today();
        $thisYear  = $this->date->copy()->setYear($today->year);

        // Kalau tahun ini sudah lewat, pakai tahun depan
        if ($thisYear->lt($today)) {
            return $thisYear->addYear();
        }

        return $thisYear;
    }

    /**
     * Berapa hari lagi sampai next_occurrence.
     * Return null kalau tidak ada tanggal.
     */
    public function getDaysUntilAttribute(): ?int
    {
        $next = $this->next_occurrence;

        if (!$next) {
            return null;
        }

        return (int) Carbon::today()->diffInDays($next, false);
    }
}