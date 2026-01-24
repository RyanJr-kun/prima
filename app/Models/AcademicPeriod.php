<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\DistributionStatus;

class AcademicPeriod extends Model
{
    protected $fillable = [
        'name',
        'is_active',

        //Distribusi-Matkul
        'distribution_status',
        'distribution_kaprodi_id',
        'distribution_kaprodi_at',
        'distribution_wadir_id',
        'distribution_wadir_at',
        'distribution_direktur_id',
        'distribution_direktur_at',

        // beban-kerja-dosen
        'bkd_status',
        'bkd_kaprodi_id',
        'bkd_kaprodi_at',
        'bkd_wadir_id',
        'bkd_wadir_at',
        'bkd_direktur_id',
        'bkd_direktur_at',

        // Kalender
        'calendar_status',
        'schedule_is_published',
        'calendar_wadir_id',
        'calendar_wadir_at',
        'calendar_direktur_id',
        'calendar_direktur_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'schedule_is_published' => 'boolean',

        'distribution_kaprodi_at' => 'datetime',
        'distribution_wadir_at' => 'datetime',
        'distribution_direktur_at' => 'datetime',

        'bkd_kaprodi_at' => 'datetime',
        'bkd_wadir_at' => 'datetime',
        'bkd_direktur_at' => 'datetime',

        'calendar_wadir_at' => 'datetime',
        'calendar_direktur_at' => 'datetime',
    ];

    public function distributionKaprodi()
    {
        return $this->belongsTo(User::class, 'distribution_kaprodi_id');
    }
    public function distributionWadir()
    {
        return $this->belongsTo(User::class, 'distribution_wadir_id');
    }
    public function distributionDirektur()
    {
        return $this->belongsTo(User::class, 'distribution_direktur_id');
    }

    public function bkdKaprodi()
    {
        return $this->belongsTo(User::class, 'bkd_kaprodi_id');
    }
    public function bkdWadir()
    {
        return $this->belongsTo(User::class, 'bkd_wadir_id');
    }
    public function bkdDirektur()
    {
        return $this->belongsTo(User::class, 'bkd_direktur_id');
    }

    public function calendarWadir()
    {
        return $this->belongsTo(User::class, 'calendar_wadir_id');
    }
    public function calendarDirektur()
    {
        return $this->belongsTo(User::class, 'calendar_direktur_id');
    }
}
