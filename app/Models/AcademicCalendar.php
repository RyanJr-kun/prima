<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicCalendar extends Model
{
    protected $fillable = [
        'academic_period_id',
        'type', // Reguler 1, Reguler 2
        'activity_name',
        'start_date',
        'end_date',
        'target_semesters',
        'description',
        'is_approved',
        'approved_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_approved' => 'boolean',
        'target_semesters' => 'array', // PENTING: Otomatis convert JSON <-> Array
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class, 'academic_period_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
