<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workload extends Model
{
    protected $table = 'workloads';

    protected $fillable = [
        'academic_period_id',
        'user_id',
        'total_sks_pendidikan',
        'total_sks_penelitian',
        'total_sks_pengabdian',
        'total_sks_penunjang',

        'conclusion'
    ];

    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function activities()
    {
        return $this->hasMany(WorkloadActivitie::class);
    }
}
