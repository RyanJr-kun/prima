<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class TimeSlots extends Model
{
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'shift',
        'day_group'
    ];

    /**
     * Accessor untuk menampilkan Label cantik di Dropdown
     * Cara Pakai: $slot->label
     * Output: "Sesi 1 (08:00 - 08:50)"
     */
    protected function label(): Attribute
    {
        return Attribute::make(
            get: fn() => "{$this->name} (" .
                Carbon::parse($this->start_time)->format('H:i') . " - " .
                Carbon::parse($this->end_time)->format('H:i') . ")"
        );
    }

    /**
     * Accessor untuk mengambil durasi dalam menit (Opsional, buat jaga-jaga)
     * Cara Pakai: $slot->duration_minutes
     */
    protected function durationMinutes(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                $start = Carbon::parse($attributes['start_time']);
                $end = Carbon::parse($attributes['end_time']);
                return $end->diffInMinutes($start);
            }
        );
    }

    /**
     * Scope Helper untuk memfilter berdasarkan Group Hari
     * Cara Pakai: TimeSlot::forDay('Friday')->get();
     */
    public function scopeForDay($query, $englishDayName, $isKaryawan = false)
    {
        $dayGroup = null;

        $targetShift = $isKaryawan ? 'malam' : 'pagi';
        $query->where('shift', $targetShift);

        if ($isKaryawan) {
            if ($englishDayName === 'Saturday') {
                $dayGroup = 'sabtu';
            } else {
                $dayGroup = 'senin_jumat';
            }
        } else {
            if ($englishDayName === 'Friday') {
                $dayGroup = 'jumat';
            } elseif ($englishDayName === 'Saturday') {
                $dayGroup = 'sabtu';
            } elseif (in_array($englishDayName, ['Monday', 'Tuesday', 'Wednesday', 'Thursday'])) {
                $dayGroup = 'senin_kamis';
            }
        }

        if ($dayGroup) {
            return $query->where('day_group', $dayGroup);
        }
        return $query->where('id', -1);
    }
}
