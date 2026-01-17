<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\DistributionStatus;

class AcademicPeriod extends Model
{
    protected $fillable = [
        'name',
        'is_active',
        'distribution_status',
        'distribution_approved_by',
        'distribution_approved_at',
        'schedule_is_published'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'schedule_is_published' => 'boolean',
        'distribution_status' => DistributionStatus::class,
        'distribution_approved_at' => 'datetime',
    ];
}
