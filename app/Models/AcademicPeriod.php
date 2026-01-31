<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\DistributionStatus;

class AcademicPeriod extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
