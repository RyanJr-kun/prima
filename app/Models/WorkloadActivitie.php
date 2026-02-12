<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkloadActivitie extends Model
{
    protected $table = 'workload_activities';

    protected $fillable = [
        'workload_id',
        'category',
        'activity_name',
        'sks_load',
        'sks_real',
        'sks_assigned',
        'is_uts_maker',
        'is_uas_maker',
        'realisasi_pertemuan',
        'description',
        'document_path'
    ];

    public function workload()
    {
        return $this->belongsTo(Workload::class);
    }
}
