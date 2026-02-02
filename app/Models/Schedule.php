<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Models\TimeSlots;

class Schedule extends Model
{
    protected $fillable = [
        'study_class_id',
        'course_distribution_id',
        'course_id',
        'room_id',
        'user_id',
        'day',
        'time_slot_ids',
        'status',
        'description'
    ];

    protected $casts = [
        'time_slot_ids' => 'array',
    ];

    // --- RELATIONS ---

    public function studyClass(): BelongsTo
    {
        return $this->belongsTo(StudyClass::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function courseDistribution(): BelongsTo
    {
        return $this->belongsTo(CourseDistribution::class);
    }


    // --- ACCESSORS (FITUR PINTAR) ---

    /**
     * Mengambil Waktu Mulai & Selesai berdasarkan TimeSlot IDs
     * Output Array: ['start' => '08:00', 'end' => '10:30']
     * * Hati-hati: Ini melakukan query ke DB. Gunakan Eager Loading jika data banyak.
     */
    public function getRealTimeAttribute()
    {
        if (empty($this->time_slot_ids)) return null;

        // Ambil data slot berdasarkan ID yang tersimpan
        // Kita gunakan cache atau static variable jika dalam loop besar untuk optimasi
        $slots = TimeSlots::whereIn('id', $this->time_slot_ids)->get();

        if ($slots->isEmpty()) return null;

        // Cari jam paling awal dan paling akhir
        $start = $slots->min('start_time'); // Misal 08:00
        $end   = $slots->max('end_time');   // Misal 10:30

        return [
            'start_formatted' => Carbon::parse($start)->format('H:i'),
            'end_formatted'   => Carbon::parse($end)->format('H:i'),
        ];
    }

    /**
     * Konversi Nama Hari Inggris ke Indonesia
     */
    public function getDayIndoAttribute()
    {
        $days = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];

        return $days[$this->day] ?? $this->day;
    }
}
